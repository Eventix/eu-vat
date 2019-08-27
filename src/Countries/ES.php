<?php
/**
 * Created for eu-vat.
 *
 * File: ES.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-14
 * Time: 16:52
 */

namespace Eventix\EuVat\Countries;

use Eventix\EuVat\Country;

/**
 * Class ES
 *
 * ES
 * Spain
 *
 * ESX9999999X    1 block of 9 characters
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class ES extends Country {

    /**
     * The ISO-3166 country code
     */
    public function code(): string {
        return 'ES';
    }

    /**
     * The name of the country
     */
    public function name(): string {
        return 'Spain';
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
        return ['A-Z0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', 'A-Z0-9'];
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

        if (preg_match('/^(?:ES)([0-9]{9})$/', $result)) {
            return null;
        }

        return $result;
    }
}
