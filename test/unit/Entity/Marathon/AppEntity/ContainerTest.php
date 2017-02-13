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
    public function testContainerSetProperly() {
        $aData = [
            "type" => "DOCKER",
            "docker" => [
                "someDockerProperties" => "dockerValue"
            ],
            "volumes" => [
                "volumeValues" => "volumeValue"
            ]
        ];

        $container = new Container($aData);

        $this->assertEquals("DOCKER", $container->type);
        $this->assertTrue(isset($container->docker));
        $this->assertTrue(isset($container->volumes));
    }

    public function testAllKeysAreCorrect() {
        $aKeys = ["docker", "volumes", "type"];
        $container = new Container();

        foreach($aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $container);
        }
    }
}
