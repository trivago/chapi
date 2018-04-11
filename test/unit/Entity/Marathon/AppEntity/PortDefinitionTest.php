<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\PortDefinition;

class PortDefinitionTest extends \PHPUnit_Framework_TestCase
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

    public function testUnknownFieldsInPortDefinition()
    {
        $jobEntity = new PortDefinition([
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
