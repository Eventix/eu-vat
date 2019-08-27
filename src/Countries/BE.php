<?php
/**
 * Created for eu-vat.
 *
 * File: BE.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-14
 * Time: 14:52
 */

namespace Eventix\EuVat\Countries;

use Eventix\EuVat\Country;

/**
 * Class BE
 *
 * BE
 * Belgium
 *
 * BE0999999999
 * 1 block of 10 digits
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class BE extends Country {

    /**
     * By default, successfully formatted codes will be prepended with the country code.
     *
     * This can be set to false when not needed, or a custom prefix should be used
     *
     * @var bool $prependCode
     */
    protected $prependCode = false;

    /**
     * The ISO-3166 country code
     */
    public function code(): string {
        return 'BE';
    }

    /**
     * The name of the country
     */
    public function name(): string {
        return 'Belgium';
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
            ['0', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9', '0-9'],
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

        if (strlen($result) === 9) {
            return $this->code() . '0' . $result;
        }

        return $this->code() . $result;
    }
}
