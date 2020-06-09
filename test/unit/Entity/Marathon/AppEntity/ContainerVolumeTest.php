<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\ContainerVolume;

class ContainerVolumeTest extends \PHPUnit\Framework\TestCase
{
    public function testAllKeysAreCorrect()
    {
        $keys = ["containerPath", "hostPath", "mode"];

        $containerVolume = new ContainerVolume();
        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $containerVolume);
        }
    }

    public function testContainerVolumeIsSetCorectly()
    {
        $data = [
            "containerPath" => "some/container/path",
            "hostPath" => "some/host/path",
            "mode" => "RW"
        ];

        $containerVolume = new ContainerVolume($data);
        $this->assertEquals("some/container/path", $containerVolume->containerPath);
        $this->assertEquals("some/host/path", $containerVolume->hostPath);
        $this->assertEquals("RW", $containerVolume->mode);
    }
}
