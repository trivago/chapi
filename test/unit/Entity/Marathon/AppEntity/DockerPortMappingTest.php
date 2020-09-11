<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;

class DockerPortMappingTest extends \PHPUnit\Framework\TestCase
{
    public function testAllKeysAreCorrect()
    {
        $keys = ["containerPort", "hostPort", "name", "protocol", "servicePort"];

        $dockerPortMapping = new DockerPortMapping();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $dockerPortMapping);
        }
    }

    public function testDockerPortMappingSetProperly()
    {
        $data = [
            "hostPort" => 10010,
            "containerPort" => 10011,
            "servicePort" => 10211,
            "protocol" => "udp" // tcp is default
        ];

        $dockerPortMapping = new DockerPortMapping($data);

        $this->assertSame(10010, $dockerPortMapping->hostPort);
        $this->assertSame(10011, $dockerPortMapping->containerPort);
        $this->assertSame(10211, $dockerPortMapping->servicePort);
        $this->assertSame("udp", $dockerPortMapping->protocol);
    }
}
