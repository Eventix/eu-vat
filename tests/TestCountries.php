<?php
/**
 * Created for eu-vat.
 *
 * File: TestCountries.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-13
 * Time: 17:24
 */

namespace Eventix\EuVat\Tests;

use Eventix\EuVat\Countries;

/**
 * Class TestCountries
 *
 * Specifically for adding the test country to the supported list.
 *
 * Note, it is not guaranteed that (after more development) there is a supported country without a custom formatter.
 * Quadruple character country codes are not supported in either ISO-3166-1 or ISO-3166-2.
 * Such a value is added to the supported country list on testing for this specific purpose.
 *
 * ZZZZ is designated as a supported test country
 * AAAA is also reserved as an unsupported test country
 *
 * @package Eventix\EuVatEventix\EuVateu-vat
 * @author Peter de Kok <peter@eventix.io>
 */
class TestCountries extends Countries {

    public function __construct() {
        static::$countries['ZZZZ'] = TestCountry::class;

        parent::__construct();
    }
}
