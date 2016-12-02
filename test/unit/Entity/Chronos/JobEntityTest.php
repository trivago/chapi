<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-05
 *
 */

namespace unit\Entity\Chronos;


use Chapi\Entity\Chronos\ChronosJobEntity;

class JobEntityTest extends \PHPUnit_Framework_TestCase
{
    public function testInitSuccess()
    {
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'unknownProperty' => 'value']);

        $this->assertEquals('jobname', $_oJobEntity->name);
        $this->assertFalse(property_exists($_oJobEntity, 'unknownProperty'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInitFailure()
    {
        $_oJobEntity = new ChronosJobEntity('string');
    }

    public function testInitSuccessForContainer()
    {
        $_aData = [
            'name' => 'jobname',
            'container' => [
                'type' => 'DOCKER',
                'image' => 'foo/bar',
                'network' => 'BRIDGE',
                'unknownProperty' => 'value',
                'volumes' => [
                    [
                        'containerPath' => '/var/foo',
                        'hostPath' => '/tmp/bar',
                        'mode' => 'RO'
                    ]
                ]
            ]
        ];
        $_oJobEntity = new JobEntity($_aData);

        $this->assertEquals('jobname', $_oJobEntity->name);
        $this->assertEquals('docker', $_oJobEntity->container->type);
        $this->assertEquals('foo/bar', $_oJobEntity->container->image);
        $this->assertTrue(is_array($_oJobEntity->container->volumes));
        $this->assertFalse(property_exists($_oJobEntity->container, 'unknownProperty'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInitFailureForContainer()
    {
        $_aData = [
            'name' => 'jobname',
            'container' => 'foo'
        ];
        $_oJobEntity = new JobEntity($_aData);
    }
    
    public function testGetSimpleArrayCopy()
    {
        $_aParents = ['jobA', 'jobB'];
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'parents' => $_aParents]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();

        $this->assertEquals(json_encode($_aParents), $_aSimpleArrayCopy['parents']);
    }

    public function testGetSimpleArrayCopyWithUris()
    {
        $_aUris = ['http://a.url.com', 'http://b.url.com'];
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'uris' => $_aUris]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();

        $this->assertEquals(json_encode($_aUris), $_aSimpleArrayCopy['uris']);
    }

    public function testGetSimpleArrayCopyWithEnvironmentVariables()
    {
        $_oEnvironmentVariables = new \stdClass();
        $_oEnvironmentVariables->FOO = 'bar';

        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'environmentVariables' => [$_oEnvironmentVariables]]);

        $_aSimpleArrayCopy = $_oJobEntity->getSimpleArrayCopy();
        $this->assertEquals(json_encode([$_oEnvironmentVariables]), $_aSimpleArrayCopy['environmentVariables']);
    }

    public function testScheduleJobJsonSerialize()
    {
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D']);

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
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'parents' => ['jobA', 'jobB']]);

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
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D']);

        $this->assertTrue($_oJobEntity->isSchedulingJob());
        $this->assertFalse($_oJobEntity->isDependencyJob());
    }

    public function testIsDependencyJob()
    {
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'parents' => ['parentjob']]);
        $this->assertTrue($_oJobEntity->isDependencyJob());
        $this->assertFalse($_oJobEntity->isSchedulingJob());
    }

    public function testNeitherNorJob()
    {
        $_oJobEntity = new ChronosJobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D', 'parents' => ['parentjob']]);
        $this->assertFalse($_oJobEntity->isDependencyJob());
        $this->assertFalse($_oJobEntity->isSchedulingJob());
    }
}