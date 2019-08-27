<?php
/**
 * Created for eu-vat.
 *
 * File: EuVatTimeoutException.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-16
 * Time: 16:25
 */

namespace Eventix\EuVat;

use Exception;

/**
 * This exception is thrown if the service is unavailable, or a member state has some unintended problems.
 * This should not be the user's fault (or a malformed format, etc.)
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class EuVatTimeoutException extends Exception {

}
