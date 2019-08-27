<?php
/**
 * Created for eu-vat.
 *
 * File: Facade.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-15
 * Time: 14:53
 */

namespace Eventix\EuVat;

/**
 * Class Facade
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class Facade extends \Illuminate\Support\Facades\Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor() {
        return Countries::class;
    }
}
