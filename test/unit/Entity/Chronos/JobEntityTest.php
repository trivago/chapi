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
        $_aParents = ['jobA', 'jobB'];
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'parents' => $_aParents]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();

        $this->assertEquals(json_encode($_aParents), $_aSimpleArrayCopy['parents']);
    }

    public function testGetSimpleArrayCopyWithUris()
    {
        $_aUris = ['http://a.url.com', 'http://b.url.com'];
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'uris' => $_aUris]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();

        $this->assertEquals(json_encode($_aUris), $_aSimpleArrayCopy['uris']);
    }

    public function testGetSimpleArrayCopyWithEnvironmentVariables()
    {
        $_oEnvironmentVariables = new \stdClass();
        $_oEnvironmentVariables->FOO = 'bar';

        $_oJobEntity = new JobEntity(['name' => 'jobname', 'environmentVariables' => [$_oEnvironmentVariables]]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();
        $this->assertEquals(json_encode([$_oEnvironmentVariables]), $_aSimpleArrayCopy['environmentVariables']);
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

    public function testIsSchedulingJob()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D']);

        $this->assertTrue($_oJobEntity->isSchedulingJob());
        $this->assertFalse($_oJobEntity->isDependencyJob());
    }

    public function testIsDependencyJob()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'parents' => ['parentjob']]);
        $this->assertTrue($_oJobEntity->isDependencyJob());
        $this->assertFalse($_oJobEntity->isSchedulingJob());
    }

    public function testNeitherNorJob()
    {
        $_oJobEntity = new JobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D', 'parents' => ['parentjob']]);
        $this->assertFalse($_oJobEntity->isDependencyJob());
        $this->assertFalse($_oJobEntity->isSchedulingJob());
    }
}