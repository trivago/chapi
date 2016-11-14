<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\Container;
use ChapiTest\src\TestTraits\JobEntityTrait;

class ContainerTest extends AbstractValidatorTest
{
    use JobEntityTrait;
    
    public function testIsValidSuccess()
    {
        $_oPropertyValidator = new Container();
        $_oJobEntity = $this->getValidContainerJobEntity();
        
        $this->handleValidTestCase($_oPropertyValidator, 'container', null);
        $this->handleValidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);

        $_oJobEntity->container->volumes[0]->mode = 'RO';
        $this->handleValidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);

        $_oJobEntity->container->volumes[0]->mode = 'RW';
        $this->handleValidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);

        $_oJobEntity->container->volumes = [];
        $this->handleValidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);
    }

    public function testIsValidFailure()
    {
        $_oPropertyValidator = new Container();
        $_oJobEntity = $this->getValidContainerJobEntity();
        
        $this->handleInvalidTestCase($_oPropertyValidator, 'container', []);
        $this->handleInvalidTestCase($_oPropertyValidator, 'container', 1);
        $this->handleInvalidTestCase($_oPropertyValidator, 'container', 'foo');

        $_oJobEntity->container->volumes[0]->mode = 'R';
        $this->handleInvalidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);

        $_oJobEntity->container->volumes = new \stdClass();
        $this->handleInvalidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);

        $_oJobEntity->container->volumes = null;
        $this->handleInvalidTestCase($_oPropertyValidator, 'container', $_oJobEntity->container);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oPropertyValidator = new Container();
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'container', null, 1);
    }
}