<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-18
 *
 */

namespace unit\BusinessCase\Comparision;

use Chapi\BusinessCase\Comparison\MarathonJobComparisonBusinessCase;
use Chapi\Entity\Marathon\AppEntity\Container;
use Chapi\Entity\Marathon\AppEntity\Docker;
use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;
use Chapi\Entity\Marathon\AppEntity\HealthCheck;
use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\MarathonAppEntity;
use ChapiTest\src\TestTraits\AppEntityTrait;
use Prophecy\Argument;
use Symfony\Component\Console\Tests\Input\ArgvInputTest;

class MarathonJobComparisonBusinessCaseTest extends \PHPUnit_Framework_TestCase
{
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $remoteRepository;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $localRepository;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $diffCompare;

    public function setUp()
    {
        $this->remoteRepository = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->localRepository = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->diffCompare = $this->prophesize('Chapi\Component\Comparison\DiffCompareInterface');
    }


    public function testGetLocalMissingJobsSuccess()
    {
        $remoteEntities = $this->createAppCollection(['/main/id1', '/main/id2']);
        $localEntities = $this->createAppCollection(['/main/id2']);

        $this->remoteRepository
            ->getJobs()
            ->willReturn($remoteEntities);

        $this->localRepository
            ->getJobs()
            ->willReturn($localEntities);

        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $localMissingJobs = $marathonJobCompare->getLocalMissingJobs();

        $this->assertEquals(1, count($localMissingJobs), 'Expected 1 job, got ' . count($localMissingJobs));

        $gotKey = $localMissingJobs[0];
        $this->assertEquals("/main/id1", $gotKey, 'Expected ”/main/id1", received ' . $gotKey);
    }

    public function testGetRemoteMissingJobsSuccess()
    {
        $remoteEntities = $this->createAppCollection(['/main/id2']);
        $localEntities = $this->createAppCollection(['/main/id1', '/main/id2']);


        $this->remoteRepository
            ->getJobs()
            ->willReturn($remoteEntities);

        $this->localRepository
            ->getJobs()
            ->willReturn($localEntities);

        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $remoteMissingJobs = $marathonJobCompare->getRemoteMissingJobs();

        $this->assertEquals(1, count($remoteMissingJobs), 'Expected 1 job, got ' . count($remoteMissingJobs));

        $gotKey = $remoteMissingJobs[0];
        $this->assertEquals("/main/id1", $gotKey, 'Expected ”/main/id1", received ' . $gotKey);
    }

    public function testGetLocalJobUpdatesSuccess()
    {
        $localEntities = $this->createAppCollection(['/main/id2']);
        $this->localRepository
            ->getJobs()
            ->willReturn($localEntities);


        $this->localRepository
            ->getJob(Argument::exact('/main/id2'))
            ->willReturn($localEntities['/main/id2']);

        $updatedApp = clone $localEntities['/main/id2'];
        $updatedApp->cpus = 4;

        $this->remoteRepository
            ->getJobs()
            ->willReturn([$updatedApp]);

        $this->remoteRepository
            ->getJob(Argument::exact('/main/id2'))
            ->willReturn($updatedApp);

        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $updatedApps = $marathonJobCompare->getLocalJobUpdates();

        $this->assertEquals(1, count($updatedApps), 'Expected 1 job, got ' . count($updatedApps));

        $this->assertEquals('/main/id2', $updatedApps[0], 'Expected "/main/id2", received ' . $updatedApps[0]);
    }

    public function testGetLocalUpdatesCallsPreCompareModification()
    {
        $localEntity = $this->getValidMarathonAppEntity('/main/id1');

        $remoteEntity = $this->getValidMarathonAppEntity('/main/id1');
        $remoteEntity->portDefinitions = new PortDefinition(["port" => 8080]);


        $this->localRepository
            ->getJobs()
            ->willReturn([$localEntity]);

        $this->remoteRepository
            ->getJobs()
            ->willReturn([$remoteEntity]);

        $this->remoteRepository
            ->getJob(Argument::exact($remoteEntity->getKey()))
            ->willReturn($remoteEntity);


        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $gotDiff = $marathonJobCompare->getLocalJobUpdates('/main/id1');

        $expectedDiff = [];
        $this->assertEquals($expectedDiff, $gotDiff, "Expected diff doesn't matched recieved diff");
    }

    public function testGetJobDiffWithChangesInRemoteSuccess()
    {
        $localEntity = $this->getValidMarathonAppEntity('/main/id1');
        $localEntity->dependencies = ["/some/local/dep"];

        $remoteEntity = $this->getValidMarathonAppEntity('/main/id1');
        $remoteEntity->cpus = 4;
        $remoteEntity->env = new \stdClass();
        $remoteEntity->env->path = "/test/path";
        $remoteEntity->dependencies = ["/some/local/dep", "/some/dep"];

        $this->localRepository
            ->getJob(Argument::exact($localEntity->getKey()))
            ->willReturn($localEntity);

        $this->remoteRepository
            ->getJob(Argument::exact($remoteEntity->getKey()))
            ->willReturn($remoteEntity);


        $this->diffCompare
            ->compare(Argument::exact($remoteEntity->cpus), Argument::exact($localEntity->cpus))
            ->willReturn("- 4\n+ 1")
            ->shouldBeCalledTimes(1);

        $this->diffCompare
            ->compare(Argument::exact($remoteEntity->env), Argument::exact($localEntity->env))
            ->willReturn('{- "path" : "/test/path"}')
            ->shouldBeCalled();

        $this->diffCompare
            ->compare(Argument::exact($remoteEntity->dependencies), Argument::exact($localEntity->dependencies))
            ->willReturn('- []\n+ [\n+ "/some/dep"\n+ ]')
            ->shouldBeCalled();

        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $gotDiff = $marathonJobCompare->getJobDiff('/main/id1');

        $expectedDiff = [
            "cpus" => "- 4\n+ 1",
            "env" => '{- "path" : "/test/path"}',
            "dependencies" => '- []\n+ [\n+ "/some/dep"\n+ ]'
        ];
        $this->assertEquals($expectedDiff, $gotDiff, "Expected diff doesn't matched recieved diff");
    }

    public function testGetJobDiffCallsPreCompareModification()
    {
        $localEntity = $this->getValidMarathonAppEntity('/main/id1');
        $localEntity->container = new Container();
        $localEntity->container->docker = new Docker();
        $localEntity->container->docker->portMappings[] = new DockerPortMapping(["servicePort" => 0]);

        $remoteEntity = $this->getValidMarathonAppEntity('/main/id1');
        $remoteEntity->container = new Container();
        $remoteEntity->container->docker = new Docker();
        $remoteEntity->container->docker->portMappings[] = new DockerPortMapping(["servicePort" => 443]);
        $remoteEntity->portDefinitions = new PortDefinition(["port" => 8080]);

        $this->localRepository
            ->getJob(Argument::exact($localEntity->getKey()))
            ->willReturn($localEntity);

        $this->remoteRepository
            ->getJob(Argument::exact($remoteEntity->getKey()))
            ->willReturn($remoteEntity);


        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $gotDiff = $marathonJobCompare->getJobDiff('/main/id1');

        $expectedDiff = [];
        $this->assertEquals($expectedDiff, $gotDiff, "Expected diff doesn't matched recieved diff");
    }

    public function testJobDiffWithMissingRemotePortMappingSucceeds()
    {
        $localEntity = $this->getValidMarathonAppEntity('/main/id1');
        $localEntity->container = new Container();
        $localEntity->container->docker = new Docker();
        $localEntity->container->docker->portMappings[] = new DockerPortMapping(["servicePort" => 0]);

        $remoteEntity = $this->getValidMarathonAppEntity('/main/id1');
        $remoteEntity->container = new Container();
        $remoteEntity->container->docker = new Docker();
        $remoteEntity->portDefinitions = new PortDefinition(["port" => 8080]);

        $this->localRepository
            ->getJob(Argument::exact($localEntity->getKey()))
            ->willReturn($localEntity);

        $this->remoteRepository
            ->getJob(Argument::exact($remoteEntity->getKey()))
            ->willReturn($remoteEntity);


        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $gotDiff = $marathonJobCompare->getJobDiff('/main/id1');

        $expectedDiff = [
            'container' => null
        ];
        $this->assertEquals($expectedDiff, $gotDiff, "Expected diff doesn't matched recieved diff");
    }


    public function testIsJobAvailableSuccess()
    {
        $this->localRepository
            ->getJob("/main/id1")
            ->willReturn(new MarathonAppEntity());

        $this->remoteRepository
            ->getJob("/main/id1")
            ->willReturn(new MarathonAppEntity());

        $marathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->localRepository->reveal(),
            $this->remoteRepository->reveal(),
            $this->diffCompare->reveal()
        );

        $this->assertTrue($marathonJobCompare->isJobAvailable('/main/id1'));
    }

    public function testDifferentIdsAreNotEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'id',
                new MarathonAppEntity([ 'id' => 'A' ]),
                new MarathonAppEntity([ 'id' => 'B' ])
            ]
        );

        $this->assertFalse($result);
    }

    public function testDifferentLabelsAreNotEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'labels',
                new MarathonAppEntity([ 'labels' => [ 'A' => 1 ] ]),
                new MarathonAppEntity([ 'labels' => [ 'B' => 2 ] ])
            ]
        );

        $this->assertFalse($result);
    }

    public function testEqualLabelsAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'labels',
                new MarathonAppEntity([ 'labels' => [ 'A' => 1 ] ]),
                new MarathonAppEntity([ 'labels' => [ 'A' => 1 ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEqualLabelsAreEqualEvenIfIdsAreDifferent()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'labels',
                new MarathonAppEntity([ 'id' => 'A', 'labels' => [ 'A' => 1 ] ]),
                new MarathonAppEntity([ 'id' => 'B', 'labels' => [ 'A' => 1 ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testMissingLabelIsNotConsideredEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'labels',
                new MarathonAppEntity([ 'labels' => [ 'A' => 1 ] ]),
                new MarathonAppEntity([])
            ]
        );

        $this->assertFalse($result);
    }

    public function testAdditionalLabelIsNotConsideredEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'labels',
                new MarathonAppEntity([]),
                new MarathonAppEntity([ 'labels' => [ 'A' => 1 ] ])
            ]
        );

        $this->assertFalse($result);
    }

    public function testEmptyLabelsAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'labels',
                new MarathonAppEntity([]),
                new MarathonAppEntity([])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEmptyDependenciesAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'dependencies' => [] ]),
                new MarathonAppEntity([ 'dependencies' => [] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testDifferentlyTypedDependenciesAreNotEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'dependencies' => [ 'abc' ] ]),
                new MarathonAppEntity([ 'dependencies' => (object)[ '0' => 'abc' ] ])
            ]
        );

        $this->assertFalse($result);
    }

    public function testEqualDependenciesAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'dependencies' => [ 'abc' ] ]),
                new MarathonAppEntity([ 'dependencies' => [ 'abc' ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEqualDependenciesAreEqualEvenIfIdsAreDifferent()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'id' => 'A', 'dependencies' => [ 'abc' ] ]),
                new MarathonAppEntity([ 'id' => 'B', 'dependencies' => [ 'abc' ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEqualEnvsWithDifferentlyOrderedArraysAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'env',
                new MarathonAppEntity([ 'id' => 'A', 'env' => [ 'AK' => 'AV', 'BK' => 'BV' ] ]),
                new MarathonAppEntity([ 'id' => 'B', 'env' => [ 'BK' => 'BV', 'AK' => 'AV' ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEqualEnvsWithDifferentlyOrderedObjectsAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'env',
                new MarathonAppEntity([ 'id' => 'A', 'env' => (object) [ 'AK' => 'AV', 'BK' => 'BV' ] ]),
                new MarathonAppEntity([ 'id' => 'B', 'env' => (object) [ 'BK' => 'BV', 'AK' => 'AV' ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEquallyComplexDependenciesAreEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'dependencies' => [ (object)[ 'X' => 'abc' ] ] ]),
                new MarathonAppEntity([ 'dependencies' => [ (object)[ 'X' => 'abc' ] ] ])
            ]
        );

        $this->assertTrue($result);
    }

    public function testEquallyComplexDependenciesAreNotEqualIfValueIsDifferent()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'dependencies' => [ (object)[ 'X' => 'abc' ] ] ]),
                new MarathonAppEntity([ 'dependencies' => [ (object)[ 'X' => 'def' ] ] ])
            ]
        );

        $this->assertFalse($result);
    }

    public function testEquallyComplexDependenciesAreEqualIfKeyIsDifferent()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'dependencies' => [ (object)[ 'X' => 'abc' ] ] ]),
                new MarathonAppEntity([ 'dependencies' => [ (object)[ 'Y' => 'abc' ] ] ])
            ]
        );

        $this->assertFalse($result);
    }

    public function testArrayOfSubentitiesEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEqual');
        $method->setAccessible(true);

        $healthCheckA = new HealthCheck();
        $healthCheckA->port = 0;

        $healthCheckB = new HealthCheck();

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->localRepository->reveal(),
                $this->remoteRepository->reveal(),
                $this->diffCompare->reveal()
            ),
            [
                'dependencies',
                [$healthCheckA],
                [$healthCheckB]
            ]
        );

        $this->assertFalse($result);
    }
}
