<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\PortDefinition;

class PortDefinitionTest extends \PHPUnit\Framework\TestCase
{
    public function testPortDefinitionsSetProperly()
    {
        $data = (array)[
                "port" => 10000,
                "labels" => (object)["key" => "somelabel"],
                "name" => "myport",
                "protocol" => "udp"
        ];

        $portDefinition = new PortDefinition($data);

        $this->assertEquals($portDefinition->port, 10000, "Port not set correctly for portDefinitions");
        $this->assertEquals($portDefinition->name, "myport");
        $this->assertEquals($portDefinition->protocol, "udp");
        $this->assertObjectHasAttribute("key", $portDefinition->labels);
        $this->assertEquals("somelabel", $portDefinition->labels->key);
    }

    public function testAllKeysAreCorrect()
    {
        $keys = ["port", "labels", "name", "protocol"];

        $portDefinition = new PortDefinition();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $portDefinition);
        }
    }
}
