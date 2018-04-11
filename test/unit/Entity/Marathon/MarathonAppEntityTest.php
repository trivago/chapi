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

        $properties = ["id", "cmd", "cpus" , "mem", "args", "portDefinitions", "requirePorts", "instances",
                    "executor", "container", "env", "constraints", "acceptedResourceRoles", "labels", "uris",
                    "dependencies", "healthChecks", "backoffFactor", "maxLaunchDelaySeconds", "taskKillGracePeriodSeconds",
                    "upgradeStrategy"];

        $app = new MarathonAppEntity();

        foreach ($properties as $property) {
            $this->assertObjectHasAttribute($property, $app);
        }
    }

    public function testEnvIsSorted()
    {
        $app = new MarathonAppEntity(array('env' => array('b' => 'second', 'a' => 'first')));

        $this->assertEquals(get_object_vars($app->env), array('b' => 'second', 'a' => 'first')); # equality
        $this->assertEquals(array_keys(get_object_vars($app->env)), array('a', 'b')); # order
    }

    public function testUnknownFieldsInJob()
    {
        $jobEntity = new MarathonAppEntity([
            'unique_field' => "I feel like it's 2005",
            'unique_array' => ['unique', 'values']
        ]);

        $jobEntityJson = json_encode($jobEntity);
        $jobEntityTest = json_decode($jobEntityJson);

        $this->assertTrue(property_exists($jobEntityTest, 'unique_field'));
        $this->assertAttributeEquals(['unique', 'values'], 'unique_array', $jobEntityTest);

        $this->assertFalse(property_exists($jobEntityTest, 'unknownFields'));
    }
}
