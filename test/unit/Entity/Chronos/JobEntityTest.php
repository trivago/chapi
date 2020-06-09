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

class JobEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testInitSuccess()
    {
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'unknownProperty' => 'value']);

        $this->assertEquals('jobname', $jobEntity->name);
        $this->assertFalse(property_exists($jobEntity, 'unknownProperty'));
    }

    public function testInitFailure()
    {
        $this->expectException(\InvalidArgumentException::class);

        $jobEntity = new ChronosJobEntity('string');
    }

    public function testInitSuccessForContainer()
    {
        $data = [
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
        $jobEntity = new ChronosJobEntity($data);

        $this->assertEquals('jobname', $jobEntity->name);
        $this->assertEquals('docker', $jobEntity->container->type);
        $this->assertEquals('foo/bar', $jobEntity->container->image);
        $this->assertTrue(is_array($jobEntity->container->volumes));
        $this->assertFalse(property_exists($jobEntity->container, 'unknownProperty'));
    }

    public function testInitFailureForContainer()
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = [
            'name' => 'jobname',
            'container' => 'foo'
        ];
        $jobEntity = new ChronosJobEntity($data);
    }

    public function testGetSimpleArrayCopy()
    {
        $parents = ['jobA', 'jobB'];
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'parents' => $parents]);

        $simpleArrayCopy = $jobEntity->getSimpleArrayCopy();

        $this->assertEquals(json_encode($parents), $simpleArrayCopy['parents']);
    }

    public function testGetSimpleArrayCopyWithUris()
    {
        $fetch = [
            [
                'uri' => 'file:///etc/my_conf.tar.gz',
                'destPath' => '',
                'extract' => true,
                'cache' => false,
                'executable' => false
            ]
        ];
        $jobEntity = new ChronosJobEntity([
            'name' => 'jobname',
            'fetch' => $fetch
        ]);

        $simpleArrayCopy = $jobEntity->getSimpleArrayCopy();

        $this->assertEquals(json_encode($fetch), $simpleArrayCopy['fetch']);
    }

    public function testGetSimpleArrayCopyWithEnvironmentVariables()
    {
        $environmentVariables = new \stdClass();
        $environmentVariables->FOO = 'bar';

        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'environmentVariables' => [$environmentVariables]]);

        $simpleArrayCopy = $jobEntity->getSimpleArrayCopy();
        $this->assertEquals(json_encode([$environmentVariables]), $simpleArrayCopy['environmentVariables']);
    }

    public function testScheduleJobJsonSerialize()
    {
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D']);

        $this->assertInstanceOf('\JsonSerializable', $jobEntity);

        $jobEntityJson = json_encode($jobEntity);
        $jobEntityTest = json_decode($jobEntityJson);


        $this->assertEquals('jobname', $jobEntityTest->name);
        $this->assertEquals('R/2015-07-07T01:00:00Z/P1D', $jobEntityTest->schedule);

        $this->assertFalse(property_exists($jobEntityTest, 'parents'));

        $this->assertFalse(property_exists($jobEntityTest, 'successCount'));
        $this->assertFalse(property_exists($jobEntityTest, 'errorCount'));
        $this->assertFalse(property_exists($jobEntityTest, 'errorsSinceLastSuccess'));
        $this->assertFalse(property_exists($jobEntityTest, 'lastSuccess'));
        $this->assertFalse(property_exists($jobEntityTest, 'lastError'));
    }

    public function testDependencyJobJsonSerialize()
    {
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'parents' => ['jobA', 'jobB']]);

        $this->assertInstanceOf('\JsonSerializable', $jobEntity);

        $jobEntityJson = json_encode($jobEntity);
        $jobEntityTest = json_decode($jobEntityJson);


        $this->assertEquals('jobname', $jobEntityTest->name);
        $this->assertEquals(['jobA', 'jobB'], $jobEntityTest->parents);

        $this->assertFalse(property_exists($jobEntityTest, 'schedule'));
        $this->assertFalse(property_exists($jobEntityTest, 'scheduleTimeZone'));

        $this->assertFalse(property_exists($jobEntityTest, 'successCount'));
        $this->assertFalse(property_exists($jobEntityTest, 'errorCount'));
        $this->assertFalse(property_exists($jobEntityTest, 'errorsSinceLastSuccess'));
        $this->assertFalse(property_exists($jobEntityTest, 'lastSuccess'));
        $this->assertFalse(property_exists($jobEntityTest, 'lastError'));
    }

    public function testIsSchedulingJob()
    {
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D']);

        $this->assertTrue($jobEntity->isSchedulingJob());
        $this->assertFalse($jobEntity->isDependencyJob());
    }

    public function testIsDependencyJob()
    {
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'parents' => ['parentjob']]);
        $this->assertTrue($jobEntity->isDependencyJob());
        $this->assertFalse($jobEntity->isSchedulingJob());
    }

    public function testNeitherNorJob()
    {
        $jobEntity = new ChronosJobEntity(['name' => 'jobname', 'schedule' => 'R/2015-07-07T01:00:00Z/P1D', 'parents' => ['parentjob']]);
        $this->assertFalse($jobEntity->isDependencyJob());
        $this->assertFalse($jobEntity->isSchedulingJob());
    }

    public function testFetchersInJob()
    {
        $jobEntity = new ChronosJobEntity([
            'name' => 'jobname',
            'fetch' => [
                ['uri' => 'file:///etc/my_conf.tar.gz', 'extract' => true]
            ]
        ]);

        $this->assertEquals(
            1,
            count($jobEntity->fetch)
        );
        $this->assertTrue($jobEntity->fetch[0]->extract);
    }
}
