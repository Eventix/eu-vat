<?php
/**
 * Created for eu-vat.
 *
 * File: GB.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-14
 * Time: 16:52
 */

namespace Eventix\EuVat\Countries;

use Eventix\EuVat\Country;

/**
 * Class GB
 *
 * GB
 * United Kingdom
 *
 * GB999 9999 99
 * 1 block of 3 digits, 1 block of 4 digits and 1 block of 2 digits
 *
 * GB999 9999 99 999
 * 1 block of 3 digits, 1 block of 4 digits, 1 block of 2 digits and 1 block of 3 digits
 *
 * GBGD999
 * GBHA999
 * 1 block of 5 characters
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class GB extends Country {

    /**
     * The ISO-3166 country code
     */
    public function code(): string {
        return 'GB';
    }

    /**
     * The name of the country
     */
    public function name(): string {
        return 'United Kingdom';
    }

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
    public function getAllowedCharacters(): array {
        return [
            ['G', 'D', '0-9', '0-9', '0-9'],
            ['H', 'A', '0-9', '0-9', '0-9'],
            ['0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9'],
            ['0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9'],
        ];
    }

    /**
     * Try to format the input vat number to (one of the) allowed character arrays.
     *
     * @throws \Exception
     */
    public function format(string $vat, array $allowedCharacters = null): ?string {
        if (is_null($result = parent::format($vat, $allowedCharacters))) {
            return null;
        }

        if (preg_match('/^((?:GBGD|GBHA)[0-9]{3})$/', $result, $matches)) {
            return $result;
        }

        if (preg_match('/^(GB[0-9]{3})([0-9]{4})([0-9]{2})([0-9]{3})?$/', $result, $matches)) {
            // Remove the full match from the array
            array_shift($matches);

            // Concatenate the individual matches separated by spaces
            return implode(' ', $matches);
        }

        return null;
    }
}
