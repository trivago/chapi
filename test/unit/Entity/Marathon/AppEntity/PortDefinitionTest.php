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
    public function testPortDefinitionsSetProperly() {
        $portDefinition = (array)[
                "port" => 10000,
                "labels" => (object)["key" => "somelabel"],
                "name" => "myport",
                "protocol" => "udp"
        ];

        $recPortdef = new PortDefinition($portDefinition);

        $this->assertEquals($recPortdef->port, 10000, "Port not set correctly for portDefinitions");
        $this->assertEquals($recPortdef->name, "myport");
        $this->assertEquals($recPortdef->protocol, "udp");
        $this->assertObjectHasAttribute("key" , $recPortdef->labels);
        $this->assertEquals("somelabel", $recPortdef->labels->key);
    }

    public function testAllKeysAreCorrect() {
        $_aKeys = ["port", "labels", "name", "protocol"];

        $oPortDefinition = new PortDefinition();

        foreach ($_aKeys as $property) {
            $this->assertObjectHasAttribute($property, $oPortDefinition);
        }
    }

}
