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
use Chapi\Entity\Marathon\AppEntity\HealthCheck;
use Chapi\Entity\Marathon\MarathonAppEntity;
use ChapiTest\src\TestTraits\AppEntityTrait;
use Prophecy\Argument;
use Symfony\Component\Console\Tests\Input\ArgvInputTest;

class MarathonJobComparisonBusinessCaseTest extends \PHPUnit_Framework_TestCase
{
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oRemoteRepository;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLocalRepository;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oDiffCompare;

    public function setUp()
    {
        $this->oRemoteRepository = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oLocalRepository = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oDiffCompare = $this->prophesize('Chapi\Component\Comparison\DiffCompareInterface');
    }


    public function testGetLocalMissingJobsSuccess()
    {
        $_aRemoteEntities = $this->createAppCollection(['/main/id1', '/main/id2']);
        $_aLocalEntities = $this->createAppCollection(['/main/id2']);

        $this->oRemoteRepository
            ->getJobs()
            ->willReturn($_aRemoteEntities);

        $this->oLocalRepository
            ->getJobs()
            ->willReturn($_aLocalEntities);

        $oMarathonJobCompare = new MarathonJobComparisonBusinessCase(
          $this->oLocalRepository->reveal(),
            $this->oRemoteRepository->reveal(),
            $this->oDiffCompare->reveal()
        );

        $_aLocalMissingJobs = $oMarathonJobCompare->getLocalMissingJobs();

        $this->assertEquals(1, count($_aLocalMissingJobs), 'Expected 1 job, got '. count($_aLocalMissingJobs));

        $_sGotKey = $_aLocalMissingJobs[0];
        $this->assertEquals("/main/id1", $_sGotKey, 'Expected ”/main/id1", received ' . $_sGotKey);
    }

    public function testGetRemoteMissingJobsSuccess()
    {
        $_aRemoteEntities = $this->createAppCollection(['/main/id2']);
        $_aLocalEntities = $this->createAppCollection(['/main/id1', '/main/id2']);


        $this->oRemoteRepository
            ->getJobs()
            ->willReturn($_aRemoteEntities);

        $this->oLocalRepository
            ->getJobs()
            ->willReturn($_aLocalEntities);

        $oMarathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->oLocalRepository->reveal(),
            $this->oRemoteRepository->reveal(),
            $this->oDiffCompare->reveal()
        );

        $_aRemoteMissingJobs = $oMarathonJobCompare->getRemoteMissingJobs();

        $this->assertEquals(1, count($_aRemoteMissingJobs), 'Expected 1 job, got '. count($_aRemoteMissingJobs));

        $_sGotKey = $_aRemoteMissingJobs[0];
        $this->assertEquals("/main/id1", $_sGotKey, 'Expected ”/main/id1", received ' . $_sGotKey);
    }

    public function testGetLocalJobUpdatesSuccess()
    {
        $_aLocalEntities = $this->createAppCollection(['/main/id2']);
        $this->oLocalRepository
            ->getJobs()
            ->willReturn($_aLocalEntities);


        $this->oLocalRepository
            ->getJob(Argument::exact('/main/id2'))
            ->willReturn($_aLocalEntities['/main/id2']);

        $_oUpdatedApp = clone $_aLocalEntities['/main/id2'];
        $_oUpdatedApp->cpus = 4;

        $this->oRemoteRepository
            ->getJobs()
            ->willReturn([$_oUpdatedApp]);

        $this->oRemoteRepository
            ->getJob(Argument::exact('/main/id2'))
            ->willReturn($_oUpdatedApp);

        $oMarathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->oLocalRepository->reveal(),
            $this->oRemoteRepository->reveal(),
            $this->oDiffCompare->reveal()
        );

        $_aUpdatedApps = $oMarathonJobCompare->getLocalJobUpdates();

        $this->assertEquals(1, count($_aUpdatedApps), 'Expected 1 job, got '. count($_aUpdatedApps));

        $this->assertEquals('/main/id2', $_aUpdatedApps[0], 'Expected "/main/id2", received ' . $_aUpdatedApps[0]);
    }

    public function testGetJobDiffWithChangesInRemoteSuccess()
    {
        $_oLocalEntity = $this->getValidMarathonAppEntity('/main/id1');
        $_oLocalEntity->dependencies = ["/some/local/dep"];

        $_oRemoteEntity = $this->getValidMarathonAppEntity('/main/id1');
        $_oRemoteEntity->cpus = 4;
        $_oRemoteEntity->env = new \stdClass();
        $_oRemoteEntity->env->path = "/test/path";
        $_oRemoteEntity->dependencies = ["/some/local/dep", "/some/dep"];

        $this->oLocalRepository
            ->getJob(Argument::exact($_oLocalEntity->getKey()))
            ->willReturn($_oLocalEntity);

        $this->oRemoteRepository
            ->getJob(Argument::exact($_oRemoteEntity->getKey()))
            ->willReturn($_oRemoteEntity);


        $this->oDiffCompare
            ->compare(Argument::exact($_oRemoteEntity->cpus), Argument::exact($_oLocalEntity->cpus))
            ->willReturn("- 4\n+ 1")
            ->shouldBeCalledTimes(1);

        $this->oDiffCompare
            ->compare(Argument::exact($_oRemoteEntity->env), Argument::exact($_oLocalEntity->env))
            ->willReturn('{- "path" : "/test/path"}')
            ->shouldBeCalled();

        $this->oDiffCompare
            ->compare(Argument::exact($_oRemoteEntity->dependencies), Argument::exact($_oLocalEntity->dependencies))
            ->willReturn('- []\n+ [\n+ "/some/dep"\n+ ]')
            ->shouldBeCalled();

        $oMarathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->oLocalRepository->reveal(),
            $this->oRemoteRepository->reveal(),
            $this->oDiffCompare->reveal()
        );

        $_aGotDiff = $oMarathonJobCompare->getJobDiff('/main/id1');

        $_aExpectedDiff = [
            "cpus" => "- 4\n+ 1",
            "env" => '{- "path" : "/test/path"}',
            "dependencies" => '- []\n+ [\n+ "/some/dep"\n+ ]'
        ];
        $this->assertEquals($_aExpectedDiff, $_aGotDiff, "Expected diff doesn't matched recieved diff");
    }

    public function testIsJobAvailableSuccess()
    {
        $this->oLocalRepository
            ->getJob("/main/id1")
            ->willReturn(new MarathonAppEntity());

        $this->oRemoteRepository
            ->getJob("/main/id1")
            ->willReturn(new MarathonAppEntity());

        $oMarathonJobCompare = new MarathonJobComparisonBusinessCase(
            $this->oLocalRepository->reveal(),
            $this->oRemoteRepository->reveal(),
            $this->oDiffCompare->reveal()
        );

        $this->assertTrue($oMarathonJobCompare->isJobAvailable('/main/id1'));
    }

    public function testDifferentIdsAreNotEqual()
    {
        $class = new \ReflectionClass(MarathonJobComparisonBusinessCase::class);
        $method = $class->getMethod('isEntityEqual');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            new MarathonJobComparisonBusinessCase(
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
            ),
            [
                'dependencies',
                new MarathonAppEntity([ 'id' => 'A', 'dependencies' => [ 'abc' ] ]),
                new MarathonAppEntity([ 'id' => 'B', 'dependencies' => [ 'abc' ] ])
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
                $this->oLocalRepository->reveal(),
                $this->oRemoteRepository->reveal(),
                $this->oDiffCompare->reveal()
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
