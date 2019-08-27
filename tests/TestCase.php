<?php
/**
 * Created for eu-vat.
 *
 * File: TestCase.php
 * User: Peter de Kok <peter@eventix.io>
 * Date: 2019-08-14
 * Time: 09:30
 */

namespace Eventix\EuVat\Tests;

use EuVat;
use Eventix\EuVat\EuVatServiceProvider;
use Eventix\EuVat\Facade;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase
 *
 * @package Eventix\EuVat
 * @author Peter de Kok <peter@eventix.io>
 */
class TestCase extends Orchestra {

    /** @var \Eventix\EuVat\Countries */
    protected $countries;

    protected function setUp(): void {
        parent::setUp();

        // Adds the test country to the countries list,
        // Note: this works due to a static property!
        new TestCountries();

        EuVat::setTestMode(true);
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app) {
        return [EuVatServiceProvider::class];
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app) {
        return [
            'EuVat' => Facade::class,
        ];
    }
}
