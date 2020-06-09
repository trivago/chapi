<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\DockerParameters;

class DockerParametersTest extends \PHPUnit\Framework\TestCase
{
    public function testAllKeysAreCorrect()
    {
        $keys = ["key", "value"];

        $dockerParameters = new DockerParameters();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $dockerParameters);
        }
    }

    public function testDockerParameterIsSetCorrectly()
    {
        $data = ["key" => "someKey", "value" => "somevalue"];

        $dockerParameters = new DockerParameters($data);

        $this->assertEquals("someKey", $dockerParameters->key);
        $this->assertEquals("somevalue", $dockerParameters->value);
    }
}
