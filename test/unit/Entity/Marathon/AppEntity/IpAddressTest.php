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
    public function testAllKeysAreCorrect() {
        $_aKeys = ["groups", "labels", "networkName"];
        
        $oIpAddress = new IpAddress();
        foreach ($_aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oIpAddress);
        }
    }

    public function testIpAddressSetProperly() {
        $aData = [
            "groups" => ["somegroup"],
            "labels" => (object)["label1" => "somelabel"],
            "networkName" => "datacenter"
        ];

        $oIpAddress = new IpAddress($aData);
        $this->assertEquals("datacenter", $oIpAddress->networkName);
        $this->assertEquals(["somegroup"], $oIpAddress->groups);
        $this->assertTrue(isset($oIpAddress->labels));

        $this->assertObjectHasAttribute("label1", $oIpAddress->labels);
        $this->assertEquals("somelabel", $oIpAddress->labels->label1);
    }

}
