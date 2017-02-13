<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;


use Chapi\Entity\Marathon\AppEntity\HealthCheck;

class HealthCheckTest extends \PHPUnit_Framework_TestCase
{

    public function testCheckAllKeysAreCorrect() {
        $_aKeys = ["command", "gracePeriodSeconds", "intervalSeconds",
                    "maxConsecutiveFailures", "path", "port", "portIndex", "protocol" , "timeoutSeconds"];

        $oHealthCheckTest = new HealthCheck();
        foreach ($_aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oHealthCheckTest);
        }
    }

    public function testHealthCheckIsSetProperly() {
        $aData = [
            "protocol" => "HTTP",
            "path" => "/health",
            "gracePeriodSeconds" => 10,
            "intervalSeconds" => 10,
            "portIndex" => 2,
            "port" => 8081, // portIndex and port both set cause this is test for setters
            "timeoutSeconds" => 40,
            "maxConsecutiveFailures" => 4,
            "command" => ["value" => "someCommand"]
        ];

        $oHealthCheck = new HealthCheck($aData);

        $this->assertEquals("HTTP", $oHealthCheck->protocol);
        $this->assertEquals("/health", $oHealthCheck->path);
        $this->assertEquals(10, $oHealthCheck->gracePeriodSeconds);
        $this->assertEquals(10, $oHealthCheck->intervalSeconds);
        $this->assertEquals(2, $oHealthCheck->portIndex);
        $this->assertEquals(8081, $oHealthCheck->port);
        $this->assertEquals(40, $oHealthCheck->timeoutSeconds);
        $this->assertEquals(4, $oHealthCheck->maxConsecutiveFailures);
        $this->assertTrue(isset($oHealthCheck->command));
    }
}
