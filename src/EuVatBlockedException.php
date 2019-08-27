<?php
/**
 * Created for eu-vat.
 *
 * File: EuVatBlockedException.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-26
 * Time: 18:37
 */

namespace Eventix\EuVat;

use Exception;

/**
 * This exception is thrown if the IP address or VAT number are blacklisted in the validation service
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class EuVatBlockedException extends Exception {

}
