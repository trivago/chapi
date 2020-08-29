<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 10/02/17
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\HealthCheck;

class HealthCheckTest extends \PHPUnit\Framework\TestCase
{

    public function testCheckAllKeysAreCorrect()
    {
        $keys = ["command", "gracePeriodSeconds", "intervalSeconds",
                    "maxConsecutiveFailures", "path", "port", "portIndex", "protocol" , "timeoutSeconds"];

        $healthCheckTest = new HealthCheck();
        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $healthCheckTest);
        }
    }

    public function testHealthCheckIsSetProperly()
    {
        $data = [
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

        $healthCheck = new HealthCheck($data);

        $this->assertSame("HTTP", $healthCheck->protocol);
        $this->assertSame("/health", $healthCheck->path);
        $this->assertSame(10, $healthCheck->gracePeriodSeconds);
        $this->assertSame(10, $healthCheck->intervalSeconds);
        $this->assertSame(2, $healthCheck->portIndex);
        $this->assertSame(8081, $healthCheck->port);
        $this->assertSame(40, $healthCheck->timeoutSeconds);
        $this->assertSame(4, $healthCheck->maxConsecutiveFailures);
        $this->assertTrue(isset($healthCheck->command));
    }

    public function testHealthCheckGivesProperJson()
    {
        $expectedData = '{"protocol":"HTTP","path":"\/","gracePeriodSeconds":10,"intervalSeconds":10,"portIndex":0,"port":0,"timeoutSeconds":20,"maxConsecutiveFailures":3,"delaySeconds":15,"command":{"value":"someCommand"}}';

        $data = [
            "protocol" => "HTTP",
            "path" => "/",
            "gracePeriodSeconds" => 10,
            "intervalSeconds" => 10,
            "portIndex" => 0,
            "port" => 0,
            "timeoutSeconds" => 20,
            "maxConsecutiveFailures" => 3,
            "command" => ["value" => "someCommand"]
        ];

        $healthCheck = new HealthCheck($data);

        $gotData = json_encode($healthCheck);

        $this->assertSame($expectedData, $gotData);
    }

    public function testHealthCheckHasPortUnsetWithNullValue()
    {
        $expectedData = '{"protocol":"HTTP","path":"\/","gracePeriodSeconds":10,"intervalSeconds":10,"portIndex":0,"timeoutSeconds":20,"maxConsecutiveFailures":3,"delaySeconds":15,"command":{"value":"someCommand"}}';

        $data = [
            "protocol" => "HTTP",
            "path" => "/",
            "gracePeriodSeconds" => 10,
            "intervalSeconds" => 10,
            "portIndex" => 0,
            "timeoutSeconds" => 20,
            "maxConsecutiveFailures" => 3,
            "command" => ["value" => "someCommand"]
        ];

        $healthCheck = new HealthCheck($data);

        $gotData = json_encode($healthCheck);

        $this->assertSame($expectedData, $gotData);
    }
}
