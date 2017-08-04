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
        $propertyValidator = new Container();
        $jobEntity = $this->getValidContainerJobEntity();
        
        $this->handleValidTestCase($propertyValidator, 'container', null);
        $this->handleValidTestCase($propertyValidator, 'container', $jobEntity->container);

        $jobEntity->container->volumes[0]->mode = 'RO';
        $this->handleValidTestCase($propertyValidator, 'container', $jobEntity->container);

        $jobEntity->container->volumes[0]->mode = 'RW';
        $this->handleValidTestCase($propertyValidator, 'container', $jobEntity->container);

        $jobEntity->container->volumes = [];
        $this->handleValidTestCase($propertyValidator, 'container', $jobEntity->container);
    }

    public function testIsValidFailure()
    {
        $propertyValidator = new Container();
        $jobEntity = $this->getValidContainerJobEntity();
        
        $this->handleInvalidTestCase($propertyValidator, 'container', []);
        $this->handleInvalidTestCase($propertyValidator, 'container', 1);
        $this->handleInvalidTestCase($propertyValidator, 'container', 'foo');

        $jobEntity->container->volumes[0]->mode = 'R';
        $this->handleInvalidTestCase($propertyValidator, 'container', $jobEntity->container);

        $jobEntity->container->volumes = new \stdClass();
        $this->handleInvalidTestCase($propertyValidator, 'container', $jobEntity->container);

        $jobEntity->container->volumes = null;
        $this->handleInvalidTestCase($propertyValidator, 'container', $jobEntity->container);
    }

    public function testGetLastErrorMessageMulti()
    {
        $propertyValidator = new Container();
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'container', null, 1);
    }
}
