<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;


use Chapi\Entity\Marathon\AppEntity\ContainerVolume;

class ContainerVolumeTest extends \PHPUnit_Framework_TestCase
{
    public function testAllKeysAreCorrect() {
        $_aKeys = ["containerPath", "hostPath", "mode"];

        $oContainerVolume = new ContainerVolume();
        foreach ($_aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oContainerVolume);
        }
    }

    public function testContainerVolumeIsSetCorectly() {
        $aData = [
            "containerPath" => "some/container/path",
            "hostPath" => "some/host/path",
            "mode" => "RW"
        ];

        $oContainerVolume = new ContainerVolume($aData);
        $this->assertEquals("some/container/path", $oContainerVolume->containerPath);
        $this->assertEquals("some/host/path", $oContainerVolume->hostPath);
        $this->assertEquals("RW", $oContainerVolume->mode);
    }

}
