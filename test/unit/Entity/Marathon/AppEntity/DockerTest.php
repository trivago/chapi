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
use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;

class DockerTest extends \PHPUnit_Framework_TestCase
{
    public function testDockerSetProperly()
    {
        $data = [
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

        $docker = new Docker($data);

        $this->assertEquals("some/image", $docker->image);
        $this->assertEquals("BRIDGE", $docker->network);
        $this->assertEquals(true, $docker->privileged);

        $this->assertTrue(isset($docker->portMappings));
        $this->assertCount(1, $docker->portMappings);
        $this->assertContainsOnlyInstancesOf(DockerPortMapping::class, $docker->portMappings);
        $this->assertTrue(isset($docker->parameters));
    }

    public function testAllKeysAreCorrect()
    {
        $keys = ["image", "network", "privileged", "portMappings", "parameters"];

        $docker = new Docker();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $docker);
        }
    }

    public function testUnknownFieldsInDocker()
    {
        $jobEntity = new Docker([
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
