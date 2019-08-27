<?php
/**
 * Created for eu-vat.
 *
 * File: EuVatServiceProvider.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-13
 * Time: 15:11
 */

namespace Eventix\EuVat;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Validator;

/**
 * Class EuVatServiceProvider
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class EuVatServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the EU VAT service.
     *
     * @return void
     */
    public function boot(ValidationFactory $validator) {
        $app = $this->app;

        $validator->extend('vat_number', function (string $attribute, $value, array $parameter, Validator $validator) use ($app) {
            if (is_null($value) || $value === '') {
                // Empty values are considered OK (for the purpose of this rule)
                // If empty values are not desired, use the 'required' rule in conjunction with this one.
                return true;
            }

            $country = null;

            // Find if a country should be enforced.
            // The first parameter (if given) is the data field with the country code, or the country code itself.
            if (is_string(@$parameter[0]) && !empty($parameter[0]) && strtoupper($parameter[0]) !== 'NULL') {
                if (is_null($country = Arr::get($validator->getData(), $parameter[0]))) {
                    $country = $parameter[0];
                }
            }

            // Ignore the validation if there is an original value and it is the same as the current value.
            // The second parameter is the original value
            if (@$parameter[1] === $value) {
                return true;
            }

            /** @var \Eventix\EuVat\Countries $countries */
            $countries = $app->make(Countries::class);

            // Ensure the test endpoint is used for validating with VIES if this is a testing environment
            $countries->setTestMode($app->environment('testing'));

            try {
                // Ignore any not supported countries. (Validation passes)
                // The validate method returns NULL if it has no handler for the country (given or inferred)
                if (is_null($result = $countries->validate($value, $country))) {
                    $result = true;
                }

                return $result;
            } catch (EuVatBlockedException $exception) {
                // The vat number or ip address is blocked
                $validator->setCustomMessages(['vat_number' => 'validation.vat_number.blocked']);
            } catch (EuVatInvalidInputException $exception) {
                // The format is invalid, it is not only an out dated or invalid number
                $validator->setCustomMessages(['vat_number' => 'validation.vat_number.invalid_format']);
            } catch (EuVatTimeoutException $exception) {
                // If we ran out of attempts, replace default "VAT number invalid" with "try again later"
                $validator->setCustomMessages(['vat_number' => 'validation.vat_number.unresponsive']);
            }

            return false;
        }, 'validation.vat_number.invalid');
    }
}
