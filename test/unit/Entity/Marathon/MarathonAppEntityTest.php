<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */


namespace unit\Entity\Marathon;

use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\MarathonAppEntity;
use ChapiTest\src\TestTraits\AppEntityTrait;

class MarathonAppEntityTest extends \PHPUnit_Framework_TestCase
{

    public function testLiteralValuesSetWithArray()
    {
        $properties = [
            "id" => "/hello/world",
            "cmd" => "ls",
            "cpus" => 1,
            "mem" => 256,
            "instances" => 3,
            "executor" => "some_machine",
            "requirePorts" => true,
            "backoffSeconds" => 5,
            "backoffFactor" => 1.5,
            "maxLaunchDelaySeconds" => 10,
            "taskKillGracePeriodSeconds" => 10
        ];

        foreach ($properties as $property => $value) {
            $app = new MarathonAppEntity($properties);
            $this->assertEquals($app->{$property}, $value);
        }
    }

    public function testAllKeysAreCorrect()
    {

        $_aProperties = ["id", "cmd", "cpus" , "mem", "args", "portDefinitions", "requirePorts", "instances",
                    "executor", "container", "env", "constraints", "acceptedResourceRoles", "labels", "uris",
                    "dependencies", "healthChecks", "backoffFactor", "maxLaunchDelaySeconds", "taskKillGracePeriodSeconds",
                    "upgradeStrategy"];

        $app = new MarathonAppEntity();

        foreach ($_aProperties as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $app);
        }
    }
}
