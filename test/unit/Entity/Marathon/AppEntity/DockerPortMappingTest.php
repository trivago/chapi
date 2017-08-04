<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;

class DockerPortMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testAllKeysAreCorrect()
    {
        $_aKeys = ["containerPort", "hostPort", "name", "protocol", "servicePort"];

        $oDockerPortMapping = new DockerPortMapping();

        foreach ($_aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oDockerPortMapping);
        }
    }

    public function testDockerPortMappingSetProperly()
    {
        $aData = [
            "hostPort" => 10010,
            "containerPort" => 10011,
            "servicePort" => 10211,
            "protocol" => "udp" // tcp is default
        ];

        $oDockerPortMapping = new DockerPortMapping($aData);

        $this->assertEquals(10010, $oDockerPortMapping->hostPort);
        $this->assertEquals(10011, $oDockerPortMapping->containerPort);
        $this->assertEquals(10211, $oDockerPortMapping->servicePort);
        $this->assertEquals("udp", $oDockerPortMapping->protocol);
    }
}
