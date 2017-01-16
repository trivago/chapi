<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-21
 */


namespace unit\Service\JobRepository;

use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobRepository\BridgeFileSystem;
use ChapiTest\src\TestTraits\AppEntityTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

class BridgeFileSystemTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oFileSystemService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oCache;

    /** @var string */
    private $sRepositoryDir = 'BridgeFileSystemTestDir';

    /** @var  vfsStreamDirectory */
    private $oVfsRoot;

    /** @var string  */
    private $sTempTestDir = '';

    public function setUp()
    {
        $this->oFileSystemService = $this->prophesize('Symfony\Component\Filesystem\Filesystem');

        $this->oCache = $this->prophesize('Chapi\Component\Cache\CacheInterface');

        $_aStructure = array(
            'directory' => array(
                'subdirectory' => array(
                    'jobA.json' => json_encode($this->getValidScheduledJobEntity('JobA')),
                    'jobB.json' => '/* comment lines in json file */ ' . json_encode($this->getValidScheduledJobEntity('JobB')),
                ),
                'jobC.json' => json_encode($this->getValidScheduledJobEntity('JobC')),
            ),
            'jobD.json' => json_encode($this->getValidScheduledJobEntity('JobD')),
        );

        $this->oVfsRoot = vfsStream::setup($this->sRepositoryDir, null, $_aStructure);

        // init and set up temp directory
        $_sTempTestDir = sys_get_temp_dir();
        $this->sTempTestDir = $_sTempTestDir . DIRECTORY_SEPARATOR . 'ChapiUnitTest';
        if (!is_dir($this->sTempTestDir))
        {
            mkdir($this->sTempTestDir, 0755);
        }
    }

    public function testCreateInstance()
    {
        $_oFileSystemRepository = new BridgeFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir)
        );

        $this->assertInstanceOf('Chapi\Service\JobRepository\BridgeFileSystem', $_oFileSystemRepository);
    }

    public function testGetJobsSuccess()
    {
        $_oFileSystemRepository = new BridgeFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir)
        );

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            4,
            count($_aJobs)
        );

        $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', $_aJobs[0]);
    }

    public function testGetJobsSuccessWithBothMarathonAndChronosConfig()
    {
        $_aStructure = array(
            'jobD.json' => json_encode($this->getValidScheduledJobEntity('JobD')),
            'testapp.json' => json_encode($this->getValidMarathonAppEntity("/testgroup/testapp.json"))
        );

        vfsStream::setup($this->sRepositoryDir . "Merged", null, $_aStructure);

        $_oFileSystemRepository = new BridgeFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir . "Merged")
        );

        $_aEntities = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            2,
            count($_aEntities)
        );

        $_iCountMarathon = 0;
        $_iCountChronos = 0;
        /** @var JobEntityInterface $_aEntity */
        foreach ($_aEntities as $_aEntity)
        {
            $this->assertInstanceOf(
                'Chapi\Entity\JobEntityInterface',
                $_aEntity,
                'Expected to fulfill JobEntityInterface');
            if ($_aEntity->getEntityType() == JobEntityInterface::MARATHON_TYPE)
            {
                $_iCountMarathon += 1;
            }
            else if ($_aEntity->getEntityType() == JobEntityInterface::CHRONOS_TYPE)
            {
                $_iCountChronos += 1;
            }
        }

        $this->assertEquals(1, $_iCountChronos, "Expected 1 chronos job, got $_iCountChronos");
        $this->assertEquals(1, $_iCountMarathon, "Expected 1 marathon app, got $_iCountMarathon");
    }

    public function testAddUpdateRemoveChronosJobSuccess()
    {
        $_oFileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $_oFileSystemRepository = new BridgeFileSystem(
            $_oFileSystemService,
            $this->oCache->reveal(),
            $this->sTempTestDir
        );

        $_oEntity = $this->getValidScheduledJobEntity('JobX');

        // first check and init
        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($_aJobs),
            'Expected "0" jobs at first run'
        );

        // add job
        $this->assertTrue($_oFileSystemRepository->addJob($_oEntity));
        $this->assertTrue(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'JobX.json'));

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($_aJobs),
            'Expected "1" job after adding'
        );

        foreach ($_aJobs as $_oJob)
        {
            $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', $_oJob);
            $this->assertInstanceOf('Chapi\Entity\Chronos\ChronosJobEntity', $_oJob);
        }

        // update job
        $_oEntity->disabled = true;
        $_oEntity->mem = 123;

        $this->assertTrue($_oFileSystemRepository->updateJob($_oEntity));

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($_aJobs),
            'Expected still "1" job after update'
        );

        $this->assertEquals(123, $_aJobs[0]->mem);
        $this->assertTrue($_aJobs[0]->disabled);

        // remove job
        $this->assertTrue($_oFileSystemRepository->removeJob($_oEntity));
        $this->assertFalse(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'JobX.json'));

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($_aJobs),
            'Expected "0" jobs after deletion'
        );
    }

    public function testAddUpdateRemoveMarathonAppEntitySuccess()
    {
        $_oFileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $_oFileSystemRepository = new BridgeFileSystem(
            $_oFileSystemService,
            $this->oCache->reveal(),
            $this->sTempTestDir
        );

        $_oAppEntity = $this->getValidMarathonAppEntity('/testgroup/testapp');

        // first check and init
        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($_aJobs),
            'Expected "0" app at first run'
        );

        // add app
        $this->assertTrue($_oFileSystemRepository->addJob($_oAppEntity));
        $this->assertTrue(file_exists($this->sTempTestDir. DIRECTORY_SEPARATOR. 'testgroup'. DIRECTORY_SEPARATOR .'testapp.json'));

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($_aJobs),
            'Expected "1" app after adding'
        );

        foreach ($_aJobs as $_oJob)
        {
            $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', $_oJob);
            $this->assertInstanceOf('Chapi\Entity\Marathon\MarathonAppEntity', $_oJob);
        }

        // update app
        $_oAppEntity->cpus = 2;
        $_oAppEntity->mem = 1024;

        $this->assertTrue($_oFileSystemRepository->updateJob($_oAppEntity));

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($_aJobs),
            'Expected still "1" job after update'
        );

        $this->assertEquals(2, $_aJobs[0]->cpus);
        $this->assertEquals(1024, $_aJobs[0]->mem);

        // remove job
        $this->assertTrue($_oFileSystemRepository->removeJob($_oAppEntity));
        $this->assertFalse(file_exists($this->sTempTestDir . DIRECTORY_SEPARATOR . 'testgroup'. DIRECTORY_SEPARATOR .'testapp.json'));

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($_aJobs),
            'Expected "0" jobs after deletion'
        );
    }

    public function testUpdateMarathonAppEntityInGroupSuccess()
    {

        $_aStructure = array(
            'externalGroup' => array(
                'testgroup.json' => json_encode($this->getValidMarathonAppEntityGroup("/externalGroup/testgroup"))
            )
        );

        vfsStream::setup($this->sRepositoryDir, null, $_aStructure);
        $_oFileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $_oFileSystemRepository = new BridgeFileSystem(
            $_oFileSystemService,
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir)
        );

        $_aApps = $_oFileSystemRepository->getJobs();
        $this->assertEquals(2, count($_aApps), "Expected 2 app, got ".count($_aApps));

        $_sUpdatedKey = $_aApps[0]->getKey();
        $_aApps[0]->mem = 1024;
        $_aApps[0]->cpus = 2;

        $this->assertTrue($_oFileSystemRepository->updateJob($_aApps[0]), 'UpdateJob returned false, expected true');

        $_aAppsAfterModification = $_oFileSystemRepository->getJobs();
        $this->assertEquals(2, count($_aApps), "Expected 2 apps, got " . count($_aAppsAfterModification));

        foreach ($_aAppsAfterModification as $_oApp)
        {
            if ($_oApp->getKey() == $_sUpdatedKey)
            {
                $this->assertEquals(
                    1024,
                    $_oApp->mem,
                    "Expected 1024, got $_oApp->mem"
                );

                $this->assertEquals(
                    2,
                    $_oApp->cpus,
                    "Expected 2, got $_oApp->cpus"
                );
            }
        }

    }


    public function testRemoveMarathonAppEntityInGroupSuccess()
    {
        $_aGroupConfig = $this->getValidMarathonAppEntityGroup("/externalGroup/testgroup");
        $_aStructure = array(
            'externalGroup' => array(
                'testgroup.json' => json_encode($_aGroupConfig)
            )
        );

        vfsStream::setup($this->sRepositoryDir, null, $_aStructure);
        $_oFileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $_oFileSystemRepository = new BridgeFileSystem(
            $_oFileSystemService,
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir)
        );

        $_oFileSystemRepository->removeJob($_aGroupConfig["apps"][0]);

        $_aRemainingApps = $_oFileSystemRepository->getJobs();

        $this->assertEquals(1, count($_aRemainingApps), 'Expected 1 app remaining after removal, found ' . count($_aRemainingApps));
    }


    /**
     * @expectedException \Chapi\Exception\JobLoadException
     */
    public function testJobLoadException()
    {
        $_aStructure = array(
            'directory' => array(
                'jobA.json' => 'no-json-string',
            ),
            'jobB.json' => '{invalid-json: true',
        );

        $this->oVfsRoot = vfsStream::setup($this->sRepositoryDir, null, $_aStructure);

        $_oFileSystemRepository = new BridgeFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir)
        );

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertNull($_aJobs);
    }

    /**
     * @expectedException \Chapi\Exception\JobLoadException
     */
    public function testJobLoadExceptionForDuplicateJobNames()
    {
        $_aStructure = array(
            'directory' => array(
                'jobA.json' => json_encode($this->getValidScheduledJobEntity('JobA')),
            ),
            'jobB.json' => json_encode($this->getValidScheduledJobEntity('JobA')),
        );

        $this->oVfsRoot = vfsStream::setup($this->sRepositoryDir, null, $_aStructure);

        $_oFileSystemRepository = new BridgeFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            vfsStream::url($this->sRepositoryDir)
        );

        $_aJobs = $_oFileSystemRepository->getJobs();
        $this->assertNull($_aJobs);
    }
}