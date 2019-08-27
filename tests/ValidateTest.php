<?php
/**
 * Created for eu-vat.
 *
 * File: ValidateTest.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-16
 * Time: 15:31
 */

namespace Eventix\EuVat\Tests;

use EuVat;
use Eventix\EuVat\EuVatBlockedException;
use Eventix\EuVat\EuVatInvalidInputException;
use Eventix\EuVat\EuVatTimeoutException;
use Exception;
use const Exception;

/**
 * Class ValidateTest
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class ValidateTest extends TestCase {

    // Specific disclaimer for this service (read: VIES - Test service)
    // -----------------------------------------
    // Here is the list of VAT Number to use to receive each kind of answer.
    // For all the other cases, The web service will responds with a "SERVICE_UNAVAILABLE" error.

    const VALID                          = '100'; // Valid request with Valid VAT Number
    const INVALID                        = '200'; // Valid request with an Invalid VAT Number
    const INVALID_INPUT                  = '201';
    const INVALID_REQUESTER_INFO         = '202';
    const SERVICE_UNAVAILABLE            = '300';
    const MS_UNAVAILABLE                 = '301';
    const TIMEOUT                        = '302';
    const VAT_BLOCKED                    = '400';
    const IP_BLOCKED                     = '401';
    const GLOBAL_MAX_CONCURRENT_REQ      = '500';
    const GLOBAL_MAX_CONCURRENT_REQ_TIME = '501';
    const MS_MAX_CONCURRENT_REQ          = '600';
    const MS_MAX_CONCURRENT_REQ_TIME     = '601';

    public function testAllCountriesTested() {
        foreach (EuVat::codes() as $countryCode) {
            if ($countryCode === 'ZZZZ') {
                continue;
            }

            $this->assertTrue(method_exists($this, 'test' . $countryCode), $countryCode . ' not tested');
        }
    }

    public function testUnsupportedCountry() {
        // AAAA is an unsupported test country code. See Eventix\EuVat\Tests\TestCountries
        $countryCode = 'AAAA';

        $this->assertFalse(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be unsupported.');

        $this->assertValidateVatNumber(null, self::VALID, $countryCode); // Valid, but unsupported country
    }

    public function testSupportedTestCountry() {
        // ZZZZ is a supported test country code. See Eventix\EuVat\Tests\TestCountries
        $countryCode = 'ZZZZ';

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported specifically for testing.');

        // This country is not supported by VIES, but it should result in a valid request
        $this->assertValidateVatNumber('INVALID_INPUT', self::VALID, $countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testAT() {
        $countryCode = 'AT';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testBE() {
        $countryCode = 'BE';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testBG() {
        $countryCode = 'BG';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testCY() {
        $countryCode = 'CY';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testCZ() {
        $countryCode = 'CZ';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testDE() {
        $countryCode = 'DE';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testDK() {
        $countryCode = 'DK';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testEE() {
        $countryCode = 'EE';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testEL() {
        $countryCode = 'EL';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testES() {
        $countryCode = 'ES';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testFI() {
        $countryCode = 'FI';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testFR() {
        $countryCode = 'FR';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testGB() {
        $countryCode = 'GB';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testHR() {
        $countryCode = 'HR';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testHU() {
        $countryCode = 'HU';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testIE() {
        $countryCode = 'IE';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testIT() {
        $countryCode = 'IT';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testLT() {
        $countryCode = 'LT';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testLU() {
        $countryCode = 'LU';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testLV() {
        $countryCode = 'LV';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testMT() {
        $countryCode = 'MT';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testNL() {
        $countryCode = 'NL';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testPL() {
        $countryCode = 'PL';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testPT() {
        $countryCode = 'PT';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testRO() {
        $countryCode = 'RO';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testSE() {
        $countryCode = 'SE';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testSI() {
        $countryCode = 'SI';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * @throws \Exception
     */
    public function testSK() {
        $countryCode = 'SK';

        $this->assertValidateAllTestVatNumbers($countryCode);
    }

    /**
     * Helper to test all test VAT numbers for a country
     *
     * @throws \Exception
     */
    protected function assertValidateAllTestVatNumbers(string $countryCode) {
        if (!EuVat::isTestMode()) {
            throw new Exception('Cannot test with test vat numbers on live endpoint, test endpoint should be used');
        }

        $this->assertTrue(EuVat::supports($countryCode), 'Country code ' . $countryCode . ' should be supported specifically for testing.');

        $this->assertValidateVatNumber(true, self::VALID, $countryCode);
        $this->assertValidateVatNumber(false, self::INVALID, $countryCode);
        $this->assertValidateVatNumber('INVALID_INPUT', self::INVALID_INPUT, $countryCode);
        $this->assertValidateVatNumber('INVALID_REQUESTER_INFO', self::INVALID_REQUESTER_INFO, $countryCode);
        $this->assertValidateVatNumber('SERVICE_UNAVAILABLE', self::SERVICE_UNAVAILABLE, $countryCode);
        $this->assertValidateVatNumber('MS_UNAVAILABLE', self::MS_UNAVAILABLE, $countryCode);
        $this->assertValidateVatNumber('TIMEOUT', self::TIMEOUT, $countryCode);
        $this->assertValidateVatNumber('VAT_BLOCKED', self::VAT_BLOCKED, $countryCode);
        $this->assertValidateVatNumber('IP_BLOCKED', self::IP_BLOCKED, $countryCode);
        $this->assertValidateVatNumber('GLOBAL_MAX_CONCURRENT_REQ', self::GLOBAL_MAX_CONCURRENT_REQ, $countryCode);
        $this->assertValidateVatNumber('GLOBAL_MAX_CONCURRENT_REQ_TIME', self::GLOBAL_MAX_CONCURRENT_REQ_TIME, $countryCode);
    }

    /**
     * Test validation of a vat number
     *
     * @param bool|string|null $expected
     * @param string           $actual
     * @param string|null      $countryCode
     */
    protected function assertValidateVatNumber($expected, string $actual, ?string $countryCode = null) {
        try {
            $validated = EuVat::validate($actual, $countryCode);
        } catch (EuVatBlockedException $exception) {
            if (is_string($expected)) {
                $this->assertEquals($expected, $exception->getMessage(), "({$countryCode}{$actual})");

                return;
            }

            $this->fail("THIS VAT NUMBER WAS ADDED TO A BLOCKED LIST [{$exception->getMessage()}] ({$countryCode}{$actual})");

            return;
        } catch (EuVatTimeoutException $exception) {
            if (is_string($expected)) {
                $this->assertEquals($expected, $exception->getMessage(), "({$countryCode}{$actual})");

                return;
            }

            $this->fail("VIES DID NOT RESPOND, TRY AGAIN LATER [{$exception->getMessage()}] ({$countryCode}{$actual})");

            return;
        } catch (EuVatInvalidInputException $exception) {
            if (is_string($expected)) {
                $this->assertEquals($expected, $exception->getMessage(), "({$countryCode}{$actual})");

                return;
            }

            $this->fail("Vat number format invalid [{$exception->getMessage()}] ({$countryCode}{$actual})");

            return;
        } catch (Exception $exception) {
            $this->fail("Validation failed due to configuration error: [{$exception->getMessage()}] ({$countryCode}{$actual})");

            return;
        }

        if (is_null($expected)) {
            $this->assertNull($validated, "({$countryCode}{$actual})");
        } else if ($expected) {
            $this->assertTrue($validated, "({$countryCode}{$actual})");
        } else {
            $this->assertFalse($validated, "({$countryCode}{$actual})");
        }
    }
}
