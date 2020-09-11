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
        $this->assertSame("some/container/path", $containerVolume->containerPath);
        $this->assertSame("some/host/path", $containerVolume->hostPath);
        $this->assertSame("RW", $containerVolume->mode);
    }
}
