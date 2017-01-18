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
}
