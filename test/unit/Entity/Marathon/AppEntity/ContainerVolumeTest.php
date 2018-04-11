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

    public function testUnknownFieldsInVolume()
    {
        $jobEntity = new ContainerVolume([
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
