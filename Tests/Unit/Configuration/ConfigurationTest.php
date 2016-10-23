<?php
namespace Romm\Formz\Tests\Unit\Configuration;

use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\Formz\Configuration\Configuration;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class ConfigurationTest extends AbstractUnitTest
{

    /**
     * Checks that the static function `getConfigurationObjectServices` needed
     * by the `configuration_object` API returns a valid class.
     *
     * @test
     */
    public function configurationObjectServicesAreValid()
    {
        $serviceFactory = Configuration::getConfigurationObjectServices();

        $this->assertInstanceOf(ServiceFactory::class, $serviceFactory);

        unset($serviceFactory);
    }
}
