<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;


use Chapi\Entity\Marathon\AppEntity\Docker;
use Chapi\Entity\Marathon\AppEntity\DockerParameters;

class DockerTest extends \PHPUnit_Framework_TestCase
{
    public function testDockerSetProperly() {
        $aData = [
            "image" => "some/image",
            "network" => "BRIDGE",
            "privileged" => true,
            "portMappings" => [
                ["hostPort" => "12011"]
            ],
            "parameters" => [
                ["key" => "keyname", "value" => "somevalue"]
            ]
        ];

        $oDocker = new Docker($aData);

        $this->assertEquals("some/image", $oDocker->image);
        $this->assertEquals("BRIDGE", $oDocker->network);
        $this->assertEquals(true, $oDocker->privileged);

        $this->assertTrue(isset($oDocker->portMappings));
        $this->assertTrue(isset($oDocker->parameters));
    }

    public function testAllKeysAreCorrect() {
        $aKeys = ["image", "network", "privileged", "portMappings", "parameters"];

        $oDocker = new Docker();

        foreach ($aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oDocker);
        }
    }

}
