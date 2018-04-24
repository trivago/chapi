<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since:  2015-08-28
 *
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainerSetProperly()
    {
        $data = [
            "type" => "DOCKER",
            "docker" => [
                "someDockerProperties" => "dockerValue"
            ],
            "portMappings" => [
                ["hostPort" => "12011"]
            ],
            "volumes" => [
                "volumeValues" => "volumeValue"
            ]
        ];

        $container = new Container($data);

        $this->assertEquals("DOCKER", $container->type);
        $this->assertTrue(isset($container->docker));
        $this->assertTrue(isset($container->volumes));
        $this->assertTrue(isset($container->portMappings));
    }

    public function testAllKeysAreCorrect()
    {
        $keys = ["docker", "volumes", "type"];
        $container = new Container();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $container);
        }
    }
}
