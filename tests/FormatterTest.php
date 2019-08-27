<?php
/**
 * Created for eu-vat.
 *
 * File: FormatterTest.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-13
 * Time: 15:27
 */

namespace Eventix\EuVat\Tests;

use EuVat;
use Exception;

/**
 * Class FormatterTest
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class FormatterTest extends TestCase {

    const TEST_NONE    = 0;
    const TEST_SHORTER = 1;
    const TEST_LONGER  = 2;
    const TEST_BOTH    = self::TEST_SHORTER + self::TEST_LONGER;

    public function testAllCountriesTested() {
        foreach (EuVat::getClasses() as $countryCode => $class) {
            if (is_null($class) || $countryCode === 'ZZZZ') {
                continue;
            }

            $this->assertTrue(method_exists($this, 'test' . $countryCode), $countryCode . ' not tested');
        }
    }

    public function testUnsupportedCountry() {
        // AAAA is an unsupported test country code. See Eventix\EuVat\Tests\TestCountries
        $countryCode = 'AAAA';

        $this->assertFalse(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be unsupported.');

        $this->assertFormatVatNumber(null, '123456789', $countryCode); // Valid, but unsupported country
    }

    public function testSupportedTestCountry() {
        // ZZZZ is a supported test country code. See Eventix\EuVat\Tests\TestCountries
        $countryCode = 'ZZZZ';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported specifically for testing.');

        $this->assertFormatVatNumber('1234567890', '1234567890', $countryCode); // Valid(-ish)
        $this->assertFormatVatNumber('ZZZZ1234567890', 'ZZZZ1234567890', $countryCode); // Valid(-ish)
        $this->assertFormatVatNumber('ZZZZ1234567890', 'ZZZZ 1234 567 890', $countryCode); // Valid(-ish)
        $this->assertFormatVatNumber('ZZZZ1234567890', 'ZZZZ.1234.567.890', $countryCode); // Valid(-ish)
        $this->assertFormatVatNumber('ZZZZ1234567890', 'ZZZZ.1234...567.890', $countryCode); // Valid(-ish)
        $this->assertFormatVatNumber('ZZZZ1234567890', 'ZZZZ-12-34.-.56*7.890', $countryCode); // Valid(-ish)
        $this->assertFormatVatNumber('ZZZZ1234AAA567890', 'ZZZZ-12-34AAA56-7-890', $countryCode); // Valid(-ish)
    }

    public function testAT() {
        $countryCode = 'AT';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('ATU12345678', 'ATU12345678', $countryCode); // Valid
        $this->assertFormatVatNumber('ATU12345678', 'ATU1234567890', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('ATU12345678', 'AT U 1234 5678', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ATU12345678', 'ATU  1234.5678', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ATU12345678', 'AT.U.1234...5678', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ATU12345678', 'U12345678', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('ATU12345678', 'U 1234 5678', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('ATU12345678', 'U.12.34.56.78', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('ATU12345678', 'U-12..34-56...78', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('ATU12345678', '12345678', $countryCode); // Valid, but without prefix and U
        $this->assertFormatVatNumber('ATU12345678', '1234 5678', $countryCode); // Valid, but without prefix and U with extra characters
        $this->assertFormatVatNumber('ATU12345678', '12.34.56.78', $countryCode); // Valid, but without prefix and U with extra characters
        $this->assertFormatVatNumber('ATU12345678', '12..34.56...78', $countryCode); // Valid, but without prefix and U with extra characters
        $this->assertFormatVatNumber('ATU12345678', 'AT12345678', $countryCode); // Valid, but without U
        $this->assertFormatVatNumber('ATU12345678', 'AT.1234.5678', $countryCode); // Valid, but without U

        $this->assertFormatVatNumber(null, 'ATU1234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'U1234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '1234', $countryCode); // Invalid, too short
    }

    public function testBE() {
        $countryCode = 'BE';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('BE0123456789', 'BE0123456789', $countryCode); // Valid
        $this->assertFormatVatNumber('BE0123456789', 'BE012345678901', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('BE0123456789', 'BE 0 1234 56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('BE0123456789', 'BE0  1234.56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('BE0123456789', 'BE.0.1234...56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('BE0123456789', '0123456789', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('BE0123456789', '0 1234 56789', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('BE0123456789', '0.12.34.56.789', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('BE0123456789', '0-12..34-56...789', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('BE0123456789', '123456789', $countryCode); // Valid, but without prefix and 0
        $this->assertFormatVatNumber('BE0123456789', '1234 56789', $countryCode); // Valid, but without prefix and 0 with extra characters
        $this->assertFormatVatNumber('BE0123456789', '12.34.56.789', $countryCode); // Valid, but without prefix and 0 with extra characters
        $this->assertFormatVatNumber('BE0123456789', '12..34.56...789', $countryCode); // Valid, but without prefix and 0 with extra characters
        $this->assertFormatVatNumber('BE0123456789', 'BE123456789', $countryCode); // Valid, but without 0
        $this->assertFormatVatNumber('BE0123456789', 'BE.1234.56789', $countryCode); // Valid, but without 0

        $this->assertFormatVatNumber(null, 'BE01234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '01234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '1234', $countryCode); // Invalid, too short
    }

    public function testBG() {
        $this->assertFormatWithOnlyDigits('BG', 9, self::TEST_SHORTER);
        $this->assertFormatWithOnlyDigits('BG', 10, self::TEST_LONGER);
    }

    public function testCY() {
        $countryCode = 'CY';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('CY12345678A', 'CY12345678A', $countryCode); // Valid
        $this->assertFormatVatNumber('CY12345678A', 'CY123456789999AAA', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('CY12345678A', 'CY 1234 5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('CY12345678A', 'CY  1234.5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('CY12345678A', 'CY.1234...5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('CY12345678A', '12345678A', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('CY12345678A', ' 1234 5678 A', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('CY12345678A', ' 1234 5678 99 AA', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('CY12345678A', '.12.34.56.78.A', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('CY12345678A', '-12..34-56...78..A', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber(null, 'CY1234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '1234', $countryCode); // Invalid, too short
    }

    public function testCZ() {
        $this->assertFormatWithOnlyDigits('CZ', 8, self::TEST_SHORTER);
        $this->assertFormatWithOnlyDigits('CZ', 9, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('CZ', 10, self::TEST_LONGER);
    }

    public function testDE() {
        $this->assertFormatWithOnlyDigits('DE', 9);
    }

    public function testDK() {
        $countryCode = 'DK';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('DK12 34 56 78', 'DK12 34 56 78', $countryCode); // Valid
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK12 34 56 7890', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK  12 34 .5.6 78', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK12345678', $countryCode); // Valid, but without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK1234567890', $countryCode); // Valid, but extra digits without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK 1234 5678', $countryCode); // Valid, but extra characters without all block separators
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK  1234.5678', $countryCode); // Valid, but extra characters without all block separators
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK.1234.5678', $countryCode); // Valid, but extra characters without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK.1234...5678', $countryCode); // Valid, but extra characters without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', 'DK.1234.DK.5678', $countryCode); // Valid, but extra characters without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', '12 34 56 78', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('DK12 34 56 78', '12345678', $countryCode); // Valid, but without prefix without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', '12 34  56  78', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('DK12 34 56 78', '12.34.56.78', $countryCode); // Valid, but without prefix with extra characters without block separators
        $this->assertFormatVatNumber('DK12 34 56 78', '12..34-56...78', $countryCode); // Valid, but without prefix with extra characters without block separators

        $this->assertFormatVatNumber(null, 'DK01234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '1234', $countryCode); // Invalid, too short
    }

    public function testEE() {
        $this->assertFormatWithOnlyDigits('EE', 9);
    }

    public function testEL() {
        $this->assertFormatWithOnlyDigits('EL', 9);
    }

    public function testES() {
        $countryCode = 'ES';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('ES12345678A', 'ES12345678A', $countryCode); // Valid
        $this->assertFormatVatNumber('ES12345678A', 'ES12345678A01', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('ES12345678A', '.ES 1234 5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ES12345678A', 'ES 1234 5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ES12345678A', 'ES  1234.5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ES12345678A', 'ES.1234.5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ES12345678A', 'ES.1234...5678A', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('ES12345678A', '12345678A', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('ES12345678A', '1234 5678A', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('ES12345678A', '12.34.56.78A', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('ES12345678A', '12..34-56...78A', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber('ESA23456789', 'ESA23456789', $countryCode); // Valid
        $this->assertFormatVatNumber('ESA23456789', 'ES.A.234.56.789', $countryCode); // Valid
        $this->assertFormatVatNumber('ESA23456789', 'ES A 234 56 789', $countryCode); // Valid

        $this->assertFormatVatNumber(null, 'ES01234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '1234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'ES123456789', $countryCode); // Invalid, first and last position both digits
    }

    public function testFI() {
        $this->assertFormatWithOnlyDigits('FI', 8);
    }

    public function testFR() {
        $countryCode = 'FR';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('FRAB 123456789', 'FRAB 123456789', $countryCode); // Valid
        $this->assertFormatVatNumber('FRAB 123456789', 'FRAB123456789', $countryCode); // Valid, but without separator
        $this->assertFormatVatNumber('FRAB 123456789', 'FRAB12345678901', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('FRAB 123456789', 'FRAB 1234 56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('FRAB 123456789', 'FR AB  1234.56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('FRAB 123456789', 'FR.AB.1234.56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('FRAB 123456789', 'FR..AB.1234...56789', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('FRAB 123456789', 'AB123456789', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('FRAB 123456789', 'AB 1234 56789', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('FRAB 123456789', 'AB.12.34.56.789', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('FRAB 123456789', 'AB..12..34-56...789', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber('FR21 123456789', 'FR21 123456789', $countryCode); // Valid
        $this->assertFormatVatNumber('FR21 123456789', 'FR21123456789', $countryCode); // Valid, but without separator
        $this->assertFormatVatNumber('FR21 123456789', 'FR..21.1234...56789', $countryCode); // Valid, but extra characters

        $this->assertFormatVatNumber(null, 'FRAB1234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'FR123456789', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'FRAB3456789', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'AB1234', $countryCode); // Invalid, too short
    }

    public function testGB() {
        $countryCode = 'GB';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('GBGD123', 'GBGD123', $countryCode); // Valid
        $this->assertFormatVatNumber('GBGD123', 'GBGD12345', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('GBGD123', 'GB GD 123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBGD123', 'GB G D  123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBGD123', 'GB-GD-123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBGD123', 'GB.GD.123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBGD123', 'GB..GD.-.123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBGD123', 'GD123', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('GBGD123', 'GD12345', $countryCode); // Valid, but without prefix extra digits
        $this->assertFormatVatNumber('GBGD123', 'GD.123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBGD123', 'GD.-.123', $countryCode); // Valid, but extra characters

        $this->assertFormatVatNumber('GBHA123', 'GBHA123', $countryCode); // Valid
        $this->assertFormatVatNumber('GBHA123', 'GBHA12345', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('GBHA123', 'GB HA 123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBHA123', 'GB H A  123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBHA123', 'GB-HA-123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBHA123', 'GB.HA.123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBHA123', 'GB..HA.-.123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBHA123', 'HA123', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('GBHA123', 'HA12345', $countryCode); // Valid, but without prefix extra digits
        $this->assertFormatVatNumber('GBHA123', 'HA.123', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GBHA123', 'HA.-.123', $countryCode); // Valid, but extra characters

        $this->assertFormatVatNumber('GB123 4567 89', 'GB123 4567 89', $countryCode); // Valid
        $this->assertFormatVatNumber('GB123 4567 89', 'GB123456789', $countryCode); // Valid, without separators
        $this->assertFormatVatNumber('GB123 4567 89', 'GB 123  4567  89', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89', 'GB-123-4567-89', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89', 'GB.123.4567.89', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89', 'GB.-.123--4567##89', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89', '123 4567 89', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('GB123 4567 89', '123456789', $countryCode); // Valid, but without prefix and separators
        $this->assertFormatVatNumber('GB123 4567 89', '123.4567.89', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89', '123.-.4567#89', $countryCode); // Valid, but extra characters

        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB123 4567 89 012', $countryCode); // Valid
        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB123456789012', $countryCode); // Valid, without separators
        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB123 4567 89 012 345', $countryCode); // Valid, with extra digits
        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB 123  4567  89  012', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB-123-4567-89-012', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB.123.4567.89.012', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89 012', 'GB.-.123--4567##89++012', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89 012', '123 4567 89 012', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('GB123 4567 89 012', '123456789012', $countryCode); // Valid, but without prefix and separators
        $this->assertFormatVatNumber('GB123 4567 89 012', '123.4567.89.012', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('GB123 4567 89 012', '123.-.4567#89+012', $countryCode); // Valid, but extra characters

        $this->assertFormatVatNumber(null, 'GB1234', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'GBGD12', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'GBHA12', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'GD12', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, 'HA12', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '123', $countryCode); // Invalid, too short
        $this->assertFormatVatNumber(null, '123', $countryCode); // Invalid, too short
    }

    public function testHR() {
        $this->assertFormatWithOnlyDigits('HR', 11);
    }

    public function testHU() {
        $this->assertFormatWithOnlyDigits('HU', 8);
    }

    public function testIE() {
        $countryCode = 'IE';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('IE1234567WI', 'IE1234567WI', $countryCode); // Valid
        $this->assertFormatVatNumber('IE1234567WI', 'IE1234567WI01', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1234567WI', 'IE123456789WI', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1234567WI', 'IE 1234 567 WI', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1234567WI', 'IE  1234.567 WI', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1234567WI', 'IE.1234...567..WI', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1234567WI', '1234567WI', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('IE1234567WI', '1234 567 WI', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1234567WI', '12.34.56.7.WI', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1234567WI', '12..34-56...7#WI', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber('IE1234567Z', 'IE1234567Z', $countryCode); // Valid
        $this->assertFormatVatNumber('IE1234567Z', 'IE1234567Z01', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1234567Z', 'IE123456789Z', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1234567Z', 'IE 1234 567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1234567Z', 'IE  1234.567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1234567Z', 'IE.1234...567..Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1234567Z', '1234567Z', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('IE1234567Z', '1234 567 Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1234567Z', '12.34.56.7.Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1234567Z', '12..34-56...7#Z', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber('IE1A34567Z', 'IE1A34567Z', $countryCode); // Valid
        $this->assertFormatVatNumber('IE1A34567Z', 'IE1A34567Z01', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1A34567Z', 'IE1A3456789Z', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1A34567Z', 'IE 1A34 567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1A34567Z', 'IE  1A34.567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1A34567Z', 'IE.1A34...567..Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1A34567Z', '1A34567Z', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('IE1A34567Z', '1A34 567 Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1A34567Z', '1A.34.56.7.Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1A34567Z', '1A..34-56...7#Z', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber('IE1+34567Z', 'IE1+34567Z', $countryCode); // Valid
        $this->assertFormatVatNumber('IE1+34567Z', 'IE1+34567Z01', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1+34567Z', 'IE1+3456789Z', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1+34567Z', 'IE 1+34 567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1+34567Z', 'IE  1+34.567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1+34567Z', 'IE.1+34...567..Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1+34567Z', '1+34567Z', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('IE1+34567Z', '1+34 567 Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1+34567Z', '1+.34.56.7.Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1+34567Z', '1+..34-56.+.7#Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1+34567Z', '1+++34-56.+.7#Z', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber('IE1*34567Z', 'IE1*34567Z', $countryCode); // Valid
        $this->assertFormatVatNumber('IE1*34567Z', 'IE1*34567Z01', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1*34567Z', 'IE1*3456789Z', $countryCode); // Valid, but extra digits
        $this->assertFormatVatNumber('IE1*34567Z', 'IE 1*34 567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1*34567Z', 'IE  1*34.567 Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1*34567Z', 'IE.1*34...567..Z', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('IE1*34567Z', '1*34567Z', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('IE1*34567Z', '1*34 567 Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1*34567Z', '1*.34.56.7.Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1*34567Z', '1*..34-56.*.7#Z', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('IE1*34567Z', '1***34-56.*.7#Z', $countryCode); // Valid, but without prefix with extra characters
    }

    public function testIT() {
        $this->assertFormatWithOnlyDigits('IT', 11);
    }

    public function testLT() {
        $this->assertFormatWithOnlyDigits('LT', 9, self::TEST_BOTH); // 9 + 2 < 12, so ok to test longer as well
        $this->assertFormatWithOnlyDigits('LT', 12, self::TEST_LONGER);
    }

    public function testLU() {
        $this->assertFormatWithOnlyDigits('LU', 8);
    }

    public function testLV() {
        $this->assertFormatWithOnlyDigits('LV', 11);
    }

    public function testMT() {
        $this->assertFormatWithOnlyDigits('MT', 8);
    }

    public function testNL() {
        $countryCode = 'NL';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $this->assertFormatVatNumber('NL123456789B01', 'NL123456789B01', $countryCode); // Valid
        $this->assertFormatVatNumber('NL123456789B01', 'NL 1234 567 89 B01', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('NL123456789B01', 'NL 1234.567.89 B01', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('NL123456789B01', 'NL.1234.567...89.B01', $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber('NL123456789B01', '123456789B01', $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber('NL123456789B01', '1234 567 89 B01', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('NL123456789B01', '1234.567.89.B01', $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber('NL123456789B01', '1234.567...89.B01', $countryCode); // Valid, but without prefix with extra characters

        $this->assertFormatVatNumber(null, 'NL123456789', $countryCode); // Invalid, missing Bxx
        $this->assertFormatVatNumber(null, '123456789', $countryCode); // Invalid, missing Bxx
        $this->assertFormatVatNumber(null, 'NL3456789B01', $countryCode); // Invalid, too short
    }

    public function testPL() {
        $this->assertFormatWithOnlyDigits('PL', 10);
    }

    public function testPT() {
        $this->assertFormatWithOnlyDigits('PT', 9);
    }

    public function testRO() {
        $this->assertFormatWithOnlyDigits('RO', 2, self::TEST_SHORTER);
        $this->assertFormatWithOnlyDigits('RO', 3, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 4, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 5, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 6, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 7, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 8, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 9, self::TEST_NONE);
        $this->assertFormatWithOnlyDigits('RO', 10, self::TEST_LONGER);
    }

    public function testSE() {
        $this->assertFormatWithOnlyDigits('SE', 12);
    }

    public function testSI() {
        $this->assertFormatWithOnlyDigits('SI', 8);
    }

    public function testSK() {
        $this->assertFormatWithOnlyDigits('SK', 10);
    }

    /**
     * Helper to easily test variations of digit only VAT numbers
     *
     * When one country can have multiple lengths, the 'shorter' test can be skipped for every but the smallest format
     */
    protected function assertFormatWithOnlyDigits(string $countryCode, int $length, int $testSizes = self::TEST_BOTH) {
        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported.');

        $base = '123456789012345678901234567890123456789012345678901234567890';
        $valid = substr($base, 0, $length);

        $expected = $countryCode . $valid;

        $this->assertFormatVatNumber($expected, $countryCode . $valid, $countryCode); // Valid
        $this->assertFormatVatNumber($expected, $countryCode . preg_replace('/([0-9])/', ' $1', $valid), $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber($expected, $countryCode . '   ' . preg_replace('/([0-9])/', '.$1', $valid), $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber($expected, $countryCode . preg_replace('/([0-9])/', '.$1', $valid), $countryCode); // Valid, but extra characters
        $this->assertFormatVatNumber($expected, $countryCode . preg_replace('/([0-9])/', '+$1', $valid), $countryCode); // Valid, but extra characters

        $this->assertFormatVatNumber($expected, $countryCode . $valid); // Valid
        $this->assertFormatVatNumber($expected, $countryCode . preg_replace('/([0-9])/', ' $1', $valid)); // Valid, but extra characters
        $this->assertFormatVatNumber($expected, $countryCode . '   ' . preg_replace('/([0-9])/', '.$1', $valid)); // Valid, but extra characters
        $this->assertFormatVatNumber($expected, $countryCode . preg_replace('/([0-9])/', '.$1', $valid)); // Valid, but extra characters
        $this->assertFormatVatNumber($expected, $countryCode . preg_replace('/([0-9])/', '+$1', $valid)); // Valid, but extra characters

        $this->assertFormatVatNumber($expected, $valid, $countryCode); // Valid, but without prefix
        $this->assertFormatVatNumber($expected, preg_replace('/([0-9])/', ' $1', $valid), $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber($expected, preg_replace('/([0-9])/', '.$1', $valid), $countryCode); // Valid, but without prefix with extra characters
        $this->assertFormatVatNumber($expected, preg_replace('/([0-9])/', '..#$1', $valid), $countryCode); // Valid, but without prefix with extra characters

        if ($testSizes & self::TEST_LONGER) {
            $this->assertFormatVatNumber($expected, $countryCode . substr($base, 0, $length + 2), $countryCode); // Valid, but extra digits
        }

        if ($testSizes & self::TEST_SHORTER) {
            $this->assertFormatVatNumber(null, $countryCode . substr($base, 0, $length - 1), $countryCode); // Invalid, too short
            $this->assertFormatVatNumber(null, substr($base, 0, $length - 1), $countryCode); // Invalid, too short
        }
    }

    /**
     * Test formatting of a vat number
     */
    protected function assertFormatVatNumber(?string $expected, string $actual, ?string $countryCode = null) {
        try {
            $formatted = EuVat::format($actual, $countryCode);
        } catch (Exception $exception) {
            $this->fail('Formatting failed due to configuration error: ' . $exception->getMessage());

            return;
        }

        if (is_null($expected)) {
            $this->assertNull($formatted, 'From input ' . $actual);
        } else {
            $this->assertEquals($expected, $formatted, 'From input ' . $actual);
        }
    }
}
