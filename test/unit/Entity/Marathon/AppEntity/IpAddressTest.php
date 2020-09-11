<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\IpAddress;

class IpAddressTest extends \PHPUnit\Framework\TestCase
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
        $this->assertSame("datacenter", $ipAddress->networkName);
        $this->assertEquals(["somegroup"], $ipAddress->groups);
        $this->assertTrue(isset($ipAddress->labels));

        $this->assertObjectHasAttribute("label1", $ipAddress->labels);
        $this->assertSame("somelabel", $ipAddress->labels->label1);
    }
}
