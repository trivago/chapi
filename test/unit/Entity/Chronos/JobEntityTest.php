<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-05
 *
 */

namespace unit\Entity\Chronos;


use Chapi\Entity\Chronos\JobEntity;

class JobEntityTest extends \PHPUnit_Framework_TestCase
{
    public function testInitSuccess()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'unknownProperty' => 'value']);

        $this->assertEquals('jobname', $_oJobEntity->name);
        $this->assertFalse(property_exists($_oJobEntity, 'unknownProperty'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInitFailure()
    {
        $_oJobEntity = new JobEntity('string');
    }

    public function testGetSimpleArrayCopy()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'parents' => ['jobA', 'jobB']]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();

        $this->assertEquals('jobA,jobB', $_aSimpleArrayCopy['parents']);
    }

    public function testScheduleJobJsonSerialize()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D']);

        $this->assertInstanceOf('\JsonSerializable', $_oJobEntity);

        $_sJobEntityJson = json_encode($_oJobEntity);
        $_oJobEntityTest = json_decode($_sJobEntityJson);


        $this->assertEquals('jobname', $_oJobEntityTest->name);
        $this->assertEquals('R/2015-07-07T01:00:00Z/P1D', $_oJobEntityTest->schedule);

        $this->assertFalse(property_exists($_oJobEntityTest, 'parents'));

        $this->assertFalse(property_exists($_oJobEntityTest, 'successCount'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'errorCount'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'errorsSinceLastSuccess'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'lastSuccess'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'lastError'));
    }

    public function testDependencyJobJsonSerialize()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'parents' => ['jobA', 'jobB']]);

        $this->assertInstanceOf('\JsonSerializable', $_oJobEntity);

        $_sJobEntityJson = json_encode($_oJobEntity);
        $_oJobEntityTest = json_decode($_sJobEntityJson);


        $this->assertEquals('jobname', $_oJobEntityTest->name);
        $this->assertEquals(['jobA', 'jobB'], $_oJobEntityTest->parents);

        $this->assertFalse(property_exists($_oJobEntityTest, 'schedule'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'scheduleTimeZone'));

        $this->assertFalse(property_exists($_oJobEntityTest, 'successCount'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'errorCount'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'errorsSinceLastSuccess'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'lastSuccess'));
        $this->assertFalse(property_exists($_oJobEntityTest, 'lastError'));
    }
}