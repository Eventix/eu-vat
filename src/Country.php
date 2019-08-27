<?php
/**
 * Created for eu-vat.
 *
 * File: Country.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-14
 * Time: 11:43
 */

namespace Eventix\EuVat;

use Exception;
use SoapClient;
use SoapFault;

/**
 * Class Country
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
abstract class Country {

    const VIES_TEST_URL = 'http://ec.europa.eu/taxation_customs/vies/checkVatTestService.wsdl';
    const VIES_URL      = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
    /**
     * By default, successfully formatted codes will be prepended with the country code.
     *
     * This can be set to false when not needed, or a custom prefix should be used
     *
     * @var bool $prependCode
     */
    protected $prependCode = true;
    /**
     * Test mode for VAT validation calls to VIES
     *
     * @var bool $testMode
     */
    protected $testMode = false;

    /**
     * The ISO-3166 country code
     */
    public abstract function code(): string;

    /**
     * The name of the country
     */
    public abstract function name(): string;

    /**
     * Array of allowed characters.
     *
     * If only one entry is given, the string length does not matter
     * e.g.: `['A-Z0-9']`
     *
     * If multiple entries are given, the index in the array corresponds with the format's character position
     * e.g.: `['1', '2', '3', '4']`
     *
     * Can also be a nested array of allowed characters in the order they should be validated
     * e.g.: `[['1', '2', '3', '4'], ['1', '2', '3', '4', '5', '6']]`
     */
    public abstract function getAllowedCharacters(): array;

    /**
     * Set the testMode for VAT validation calls to VIES
     */
    public function setTestMode(bool $testMode = true) {
        $this->testMode = $testMode;
    }

    /**
     * Determine if test mode for calls to VIES is enabled
     */
    public function isTestMode(): bool {
        return $this->testMode;
    }

    /**
     * Create a REGEX pattern for the given consecutive characters interspersed with other characters
     */
    protected function getFormatPattern(array $allowedCharacters = []): string {
        return array_reduce($allowedCharacters, function ($carry, $char) {
            return $carry . '(?:[^' . $char . ']*([' . $char . ']))';
        }, '');
    }

    /**
     * Tests a pattern (without delimiters) against input and returns the imploded matches
     *
     * Returns NULL if the pattern does not match.
     */
    protected function formatByPattern(string $vat, string $pattern): ?string {
        // Test if the pattern matches the input
        if (!preg_match("/$pattern/", $vat, $matches)) {
            return null;
        }

        // Remove the full match from the array
        array_shift($matches);

        // Concatenate the individual matches
        return implode('', is_array(reset($matches)) ? reset($matches) : $matches);
    }

    /**
     * Try to format the input vat number to (one of the) allowed character arrays.
     *
     * @throws \Exception
     */
    public function format(string $vat, array $allowedCharacters = null): ?string {
        // If no allowed characters provided, retrieve them.
        if (is_null($allowedCharacters)) {
            $allowedCharacters = $this->getAllowedCharacters();
        }

        // Ensure there are allowed characters
        if (count($allowedCharacters) < 1) {
            throw new Exception('No allowed characters provided, Country class is malformed! [' . get_class($this) . ']');
        }

        // Supported VAT numbers should always be uppercase
        $vat = strtoupper(ltrim($vat, " \t\n\r\0\x0B.-=+*_"));

        // Remove the country code (if applicable)
        if (substr($vat, 0, 2) === $this->code()) {
            $vat = substr($vat, 2);
        }

        if (count($allowedCharacters) === 1 && is_string(reset($allowedCharacters))) {
            // All characters formatted following the same pattern
            $pattern = implode('?', array_fill(0, 20, $this->getFormatPattern($allowedCharacters)));
        } else if (count($allowedCharacters) > 1 && is_string(reset($allowedCharacters))) {
            // All characters formatted individually
            $pattern = $this->getFormatPattern($allowedCharacters);
        } else if (is_array(reset($allowedCharacters))) {
            // Multiple possible patterns (tested and applied in order, stops on the first successful pattern)
            foreach ($allowedCharacters as $chars) {
                $result = self::format($vat, $chars);

                if (!is_null($result)) {
                    // Found a valid pattern, return the result
                    return $result;
                }
            }

            // No pattern could be applied
            return null;
        } else {
            // Malformed pattern
            throw new Exception('Allowed character specification is malformed! [' . get_class($this) . ']');
        }

        if (is_null($result = $this->formatByPattern($vat, $pattern))) {
            // The pattern could not be applied
            return null;
        }

        // Some countries call this method in their overloaded format method
        // and want to do other operations before pre-pending the country code to the formatted vat number.
        // Formatted vat numbers for other countries (in the future) might not want the country code prepended.
        return ($this->prependCode ? $this->code() : '') . $result;
    }

    /**
     * Try to validate the input vat number with VIES.
     *
     * @throws \Eventix\EuVat\EuVatTimeoutException
     * @throws \Eventix\EuVat\EuVatInvalidInputException
     * @throws \Eventix\EuVat\EuVatBlockedException
     */
    public function validate(string $vat): ?bool {
        if (substr($vat, 0, 2) === $this->code()) {
            $vat = substr($vat, 2);
        }

        // Due to some member states having issues every now and then, some failed requests will be tried multiple times.
        $maxAttempts = 3;

        // Determine which endpoint to use.
        $wsdl = $this->isTestMode() ? self::VIES_TEST_URL : self::VIES_URL;

        do {
            try {
                $client = new SoapClient($wsdl);

                $response = $client->checkVat([
                    "countryCode" => $this->code(),
                    "vatNumber"   => $vat,
                ]);

                return $response->valid;
            } catch (SoapFault $exception) {
                $lastError = $exception->getMessage();

                switch ($lastError) {
                    // No point retrying this
                    case 'INVALID_INPUT':
                    case 'INVALID_REQUESTER_INFO':
                        throw new EuVatInvalidInputException($lastError);

                    // No point retrying this
                    case 'VAT_BLOCKED':
                    case 'IP_BLOCKED':
                        throw new EuVatBlockedException($lastError);

                    // This happens after 30 sec; abort immediately to give user at least some kind of feedback
                    case 'TIMEOUT':
                        break 2;

                        break;

                    // In all other cases: retry
                    default:
                        break;
                }
            }
        } while (--$maxAttempts > 0);

        // Returning false shows default error message, this will show "try again later" message
        throw new EuVatTimeoutException($lastError);
    }
}
