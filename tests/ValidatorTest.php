<?php
/**
 * Created for eu-vat.
 *
 * File: ValidatorTest.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-16
 * Time: 19:36
 */

namespace Eventix\EuVat\Tests;

use Illuminate\Validation\Validator;

/**
 * Class ValidatorTest
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class ValidatorTest extends TestCase {

    // Specific disclaimer for this service (read: VIES - Test service)
    // -----------------------------------------
    // Here is the list of VAT Number to use to receive each kind of answer.
    // For all the other cases, The web service will responds with a "SERVICE_UNAVAILABLE" error.
    //
    const VALID                            = '100'; // Valid request with Valid VAT Number
    const INVALID                          = '200'; // Valid request with an Invalid VAT Number
    const INVALID_INPUT                    = '201';
    const INVALID_REQUESTER_INFO           = '202';
    const SERVICE_UNAVAILABLE              = '300';
    const MS_UNAVAILABLE                   = '301';
    const TIMEOUT                          = '302';
    const VAT_BLOCKED                      = '400';
    const IP_BLOCKED                       = '401';
    const GLOBAL_MAX_CONCURRENT_REQ        = '500';
    const GLOBAL_MAX_CONCURRENT_REQ_TIME   = '501';
    const MS_MAX_CONCURRENT_REQ            = '600';
    const MS_MAX_CONCURRENT_REQ_TIME       = '601';
    const VALIDATOR_MESSAGE_INVALID        = 'validation.vat_number.invalid';
    const VALIDATOR_MESSAGE_INVALID_FORMAT = 'validation.vat_number.invalid_format';
    const VALIDATOR_MESSAGE_BLOCKED        = 'validation.vat_number.blocked';
    const VALIDATOR_MESSAGE_UNRESPONSIVE   = 'validation.vat_number.unresponsive';

    public function testValidatorNLWithCountryFieldValidRequestValidVatNumber() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::VALID,
        ];

        $validator = $this->makeValidator($data);

        $this->assertTrue($validator->passes());
    }

    public function testValidatorWithCountryFieldValidRequestInvalidVatNumber() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::INVALID,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_INVALID, $validator);
    }

    public function testValidatorWithCountryFieldInvalidRequestInvalidInput() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::INVALID_INPUT,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_INVALID_FORMAT, $validator);
    }

    public function testValidatorWithCountryFieldInvalidRequestInvalidRequesterInfo() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::INVALID_REQUESTER_INFO,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_INVALID_FORMAT, $validator);
    }

    public function testValidatorWithCountryFieldInvalidRequestVatBlocked() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::VAT_BLOCKED,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_BLOCKED, $validator);
    }

    public function testValidatorWithCountryFieldInvalidRequestIpBlocked() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::IP_BLOCKED,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_BLOCKED, $validator);
    }

    public function testValidatorWithCountryFieldInvalidRequestServiceUnavailable() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::SERVICE_UNAVAILABLE,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_UNRESPONSIVE, $validator);
    }

    public function testValidatorWithCountryFieldInvalidRequestTimeout() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::TIMEOUT,
        ];

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_UNRESPONSIVE, $validator);
    }

    public function testValidatorWithCountryFieldIgnoreUnchangedValue() {
        $data = [
            'country'    => 'NL',
            'vat_number' => self::VALID,
        ];

        $validator = $this->makeValidator($data, ['vat_number' => 'vat_number:country,' . self::VALID]);

        $this->assertTrue($validator->passes());
    }

    public function testValidatorWithoutCountryInferredCountryValid() {
        $data = [
            'vat_number' => 'NL' . self::VALID,
        ];

        $validator = $this->makeValidator($data, ['vat_number' => 'vat_number']);

        $this->assertTrue($validator->passes());
    }

    public function testValidatorWithoutCountryInferredCountryInvalid() {
        $data = [
            'vat_number' => 'NL' . self::INVALID,
        ];

        $validator = $this->makeValidator($data, ['vat_number' => 'vat_number']);

        $this->assertFalse($validator->passes());
        $this->assertMessage(self::VALIDATOR_MESSAGE_INVALID, $validator);
    }

    public function testValidatorWithoutCountryInferredCountryIgnoreUnchangedValue() {
        $data = [
            'vat_number' => 'NL' . self::VALID,
        ];

        $validator = $this->makeValidator($data, ['vat_number' => 'vat_number:NULL,NL' . self::VALID]);

        $this->assertTrue($validator->passes());
    }

    public function testValidatorWithoutValueValid() {
        $data = [
            'vat_number' => null,
        ];

        $validator = $this->makeValidator($data, ['vat_number' => 'vat_number']);

        $this->assertTrue($validator->passes());
    }

    public function testValidatorWithHardcodedNLCountryValid() {
        $data = [
            'vat_number' => self::VALID,
        ];

        $validator = $this->makeValidator($data, ['vat_number' => 'vat_number:NL']);

        $this->assertTrue($validator->passes());
    }

    /**
     * Helper method to make a validator based on data and rules
     */
    protected function makeValidator(array $data = [], ?array $rules = null): Validator {
        if (is_null($rules)) {
            $rules = [
                'vat_number' => 'vat_number:country',
            ];
        }

        /** @var \Illuminate\Validation\Factory $validatorFactory */
        $validatorFactory = $this->app->make('validator');

        return $validatorFactory->make($data, $rules);
    }

    /**
     * Assert if the correct validation error message was set
     */
    protected function assertMessage(string $expected, Validator $validator) {
        $this->assertEquals($expected, $validator->errors()->first('vat_number'));
    }
}
