<?php
/**
 * Created for eu-vat.
 *
 * File: Countries.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-13
 * Time: 16:36
 */

namespace Eventix\EuVat;

use Exception;

/**
 * Class Countries
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class Countries {

    /**
     * Every supported country should have an assigned class extending the Eventix\EuVat\Country class.
     *
     * To support extension of this class, every class is explicitly named and can be overwritten.
     *
     * @var array|string[]|\Eventix\EuVat\Country[]
     */
    protected static $countries = [
        'AT' => Countries\AT::class, // Austria
        'BE' => Countries\BE::class, // Belgium
        'BG' => Countries\BG::class, // Bulgaria
        'CY' => Countries\CY::class, // Cyprus
        'CZ' => Countries\CZ::class, // Czech Republic
        'DE' => Countries\DE::class, // Germany
        'DK' => Countries\DK::class, // Denmark
        'EE' => Countries\EE::class, // Estonia
        'EL' => Countries\EL::class, // Greece
        'ES' => Countries\ES::class, // Spain
        'FI' => Countries\FI::class, // Finland
        'FR' => Countries\FR::class, // France
        'GB' => Countries\GB::class, // United Kingdom
        'HR' => Countries\HR::class, // Croatia
        'HU' => Countries\HU::class, // Hungary
        'IE' => Countries\IE::class, // Ireland
        'IT' => Countries\IT::class, // Italy
        'LT' => Countries\LT::class, // Lithuania
        'LU' => Countries\LU::class, // Luxembourg
        'LV' => Countries\LV::class, // Latvia
        'MT' => Countries\MT::class, // Malta
        'NL' => Countries\NL::class, // The Netherlands
        'PL' => Countries\PL::class, // Poland
        'PT' => Countries\PT::class, // Portugal
        'RO' => Countries\RO::class, // Romania
        'SE' => Countries\SE::class, // Sweden
        'SI' => Countries\SI::class, // Slovenia
        'SK' => Countries\SK::class, // Slovakia
    ];
    /**
     * Test mode for VAT validation calls to VIES
     *
     * @var bool $testMode
     */
    protected $testMode = false;

    public function __construct() {
        // Stubbed for future use.
        // If in the future it is decided this class needs a constructor,
        // the test classes that extend this class won't have to be changed.
    }

    /**
     * Set the testMode for VAT validation calls to VIES
     */
    public function setTestMode(bool $testMode = true) {
        $this->testMode = $testMode;
    }

    /**
     * Determine if test mode for calls to VIES is enabled
     */
    public function isTestMode(): int {
        return $this->testMode;
    }

    /**
     * Retrieve the classes of the supported countries
     */
    public function getClasses(): array {
        return static::$countries;
    }

    /**
     * Retrieve a list of all supported country codes
     */
    public function codes(): array {
        return array_keys(static::$countries);
    }

    /**
     * Determine if a country code is supported
     */
    public function supports(string $code): bool {
        // (EU) VAT number country codes are always uppercase
        $code = mb_strtoupper($code);

        return array_key_exists($code, static::$countries);
    }

    /**
     * Retrieve the country name from the country code
     *
     * Returns NULL if it is not supported.
     *
     * @throws \Exception
     */
    public function name(string $code): ?string {
        // (EU) VAT number country codes are always uppercase
        $code = mb_strtoupper($code);

        if (is_null($instance = $this->getInstance($code))) {
            return null;
        }

        return $instance->name();
    }

    /**
     * Create and retrieve an instance of a Country class, if supported
     *
     * @throws \Exception
     */
    public function getInstance(string $code): ?Country {
        // (EU) VAT number country codes are always uppercase
        $code = mb_strtoupper($code);

        // Test if the country code is supported
        if (!$this->supports($code)) {
            return null;
        }

        // Ensure the class exists
        if (is_null($class = @static::$countries[$code])) {
            throw new Exception($code . ' has been declared as supported, but does not have a class associated.');
        }

        // Ensure the class is an instance. Only instantiated if used, never instantiated twice.
        if (is_string($class)) {
            if (!class_exists($class) || !is_subclass_of($class, Country::class)) {
                throw new Exception('The class referenced for ' . $code . ' can not be initialized, it is not a subclass of Country.');
            }

            $class = new $class();

            static::$countries[$code] = $class;
        }

        // Ensure the class is an instance of Country
        if (!($class instanceof Country)) {
            throw new Exception($code . ' Country class should be initialized, but is not an instance of Country.');
        }

        // Ensure this class has the current test mode
        $class->setTestMode($this->isTestMode());

        return $class;
    }

    /**
     * Try to determine the country from the VAT number
     */
    public function inferCountry(string $vat): ?string {
        if (!preg_match('/^(?:[^A-Z]*([A-Z][A-Z]))/', $vat, $matches)) {
            // No country could be inferred, this vat is not supported (without a country as input)
            return null;
        }

        // Remove the full match from the array
        array_shift($matches);

        if (($country = reset($matches)) === false || !in_array($country, $this->codes())) {
            // No (supported) country could be inferred, this vat is not supported (without a country as input)
            return null;
        }

        return $country;
    }

    /**
     * Run the formatter for a vat number
     *
     * If a country is provided, this country's formatter will be used,
     * If not, the country is inferred as best as possible from the input.
     *
     * If the country can not be inferred or the formatter can not find the correct characters in the input, the formatter returns NULL
     *
     * @throws \Exception
     */
    public function format(string $vat, ?string $country = null): ?string {
        if (is_null($country) && is_null($country = $this->inferCountry($vat))) {
            return null;
        }

        if (is_null($formatter = $this->getInstance($country))) {
            return null;
        }

        return $formatter->format($vat);
    }

    /**
     * Run the formatter for a vat number
     *
     * If a country is provided, this country's validator will be used,
     * If not, the country is inferred as best as possible from the input.
     *
     * If the country can not be inferred, the validator returns NULL
     * If the validator fails, the validator returns FALSE
     * If the validator passes, the validator returns TRUE
     *
     * @throws \Eventix\EuVat\EuVatTimeoutException
     * @throws \Eventix\EuVat\EuVatInvalidInputException
     * @throws \Eventix\EuVat\EuVatBlockedException
     * @throws \Exception
     */
    public function validate(string $vat, ?string $country = null): ?bool {
        if (is_null($country) && is_null($country = $this->inferCountry($vat))) {
            return null;
        }

        if (is_null($validator = $this->getInstance($country))) {
            return null;
        }

        return $validator->validate($vat);
    }
}
