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

class DockerTest extends \PHPUnit\Framework\TestCase
{
    public function testDockerSetProperly()
    {
        $data = [
            "image" => "some/image",
            "privileged" => true,
            "parameters" => [
                ["key" => "keyname", "value" => "somevalue"]
            ]
        ];

        $docker = new Docker($data);

        $this->assertEquals("some/image", $docker->image);
        $this->assertEquals(true, $docker->privileged);

        $this->assertTrue(isset($docker->parameters));
    }

    public function testAllKeysAreCorrect()
    {
        $keys = ["image", "privileged", "parameters"];

        $docker = new Docker();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $docker);
        }
    }
}
