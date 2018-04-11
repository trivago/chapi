<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\IpAddress;

class IpAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testAllKeysAreCorrect()
    {
        $keys = ["groups", "labels", "networkName"];
        
        $ipAddress = new IpAddress();
        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $ipAddress);
        }
    }

    public function testIpAddressSetProperly()
    {
        $data = [
            "groups" => ["somegroup"],
            "labels" => (object)["label1" => "somelabel"],
            "networkName" => "datacenter"
        ];

        $ipAddress = new IpAddress($data);
        $this->assertEquals("datacenter", $ipAddress->networkName);
        $this->assertEquals(["somegroup"], $ipAddress->groups);
        $this->assertTrue(isset($ipAddress->labels));

        $this->assertObjectHasAttribute("label1", $ipAddress->labels);
        $this->assertEquals("somelabel", $ipAddress->labels->label1);
    }

    public function testUnknownFieldsInIpAddress()
    {
        $jobEntity = new IpAddress([
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
