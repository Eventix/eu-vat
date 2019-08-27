<?php
/**
 * Created for eu-vat.
 *
 * File: TestCountry.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-14
 * Time: 13:16
 */

namespace Eventix\EuVat\Tests;

use Eventix\EuVat\Country;

/**
 * Class TestCountry
 *
 * @package Eventix\EuVatEventix\EuVatEventix\EuVat\Tests
 * @author Peter de Kok <peter@eventix.io>
 */
class TestCountry extends Country {

    protected $prependCode = false;

    /**
     * The ISO-3166 country code
     */
    public function code(): string {
        return 'ZZZZ';
    }

    /**
     * The name of the country
     */
    public function name(): string {
        return 'Test VAT Country';
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
        return ['A-Z0-9'];
    }
}
