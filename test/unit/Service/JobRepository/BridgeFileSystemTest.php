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
    private $fileSystemService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $cache;

    /** @var string */
    private $repositoryDir = 'BridgeFileSystemTestDir';

    /** @var  vfsStreamDirectory */
    private $vfsRoot;

    /** @var string  */
    private $tempTestDir = '';

    public function setUp()
    {
        $this->fileSystemService = $this->prophesize('Symfony\Component\Filesystem\Filesystem');

        $this->cache = $this->prophesize('Chapi\Component\Cache\CacheInterface');

        $structure = array(
            'directory' => array(
                'subdirectory' => array(
                    'jobA.json' => json_encode($this->getValidScheduledJobEntity('JobA')),
                    'jobB.json' => '/* comment lines in json file */ ' . json_encode($this->getValidScheduledJobEntity('JobB')),
                ),
                'jobC.json' => json_encode($this->getValidScheduledJobEntity('JobC')),
            ),
            'jobD.json' => json_encode($this->getValidScheduledJobEntity('JobD')),
        );

        $this->vfsRoot = vfsStream::setup($this->repositoryDir, null, $structure);

        // init and set up temp directory
        $tempTestDir = sys_get_temp_dir();
        $this->tempTestDir = $tempTestDir . DIRECTORY_SEPARATOR . 'ChapiUnitTest';
        if (!is_dir($this->tempTestDir)) {
            mkdir($this->tempTestDir, 0755);
        }
    }

    public function testCreateInstance()
    {
        $fileSystemRepository = new BridgeFileSystem(
            $this->fileSystemService->reveal(),
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir)
        );

        $this->assertInstanceOf('Chapi\Service\JobRepository\BridgeFileSystem', $fileSystemRepository);
    }

    public function testGetJobsSuccess()
    {
        $fileSystemRepository = new BridgeFileSystem(
            $this->fileSystemService->reveal(),
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir)
        );

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            4,
            count($jobs)
        );

        $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', $jobs[0]);
    }

    public function testGetJobsSuccessWithBothMarathonAndChronosConfig()
    {
        $structure = array(
            'jobD.json' => json_encode($this->getValidScheduledJobEntity('JobD')),
            'testapp.json' => json_encode($this->getValidMarathonAppEntity("/testgroup/testapp.json"))
        );

        vfsStream::setup($this->repositoryDir . "Merged", null, $structure);

        $fileSystemRepository = new BridgeFileSystem(
            $this->fileSystemService->reveal(),
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir . "Merged")
        );

        $entities = $fileSystemRepository->getJobs();
        $this->assertEquals(
            2,
            count($entities)
        );

        $countMarathon = 0;
        $countChronos = 0;
        /** @var JobEntityInterface $entity */
        foreach ($entities as $entity) {
            $this->assertInstanceOf(
                'Chapi\Entity\JobEntityInterface',
                $entity,
                'Expected to fulfill JobEntityInterface'
            );
            if ($entity->getEntityType() == JobEntityInterface::MARATHON_TYPE) {
                $countMarathon += 1;
            } elseif ($entity->getEntityType() == JobEntityInterface::CHRONOS_TYPE) {
                $countChronos += 1;
            }
        }

        $this->assertEquals(1, $countChronos, "Expected 1 chronos job, got $countChronos");
        $this->assertEquals(1, $countMarathon, "Expected 1 marathon app, got $countMarathon");
    }

    public function testAddUpdateRemoveChronosJobSuccess()
    {
        $fileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $fileSystemRepository = new BridgeFileSystem(
            $fileSystemService,
            $this->cache->reveal(),
            $this->tempTestDir
        );

        $entity = $this->getValidScheduledJobEntity('JobX');

        // first check and init
        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($jobs),
            'Expected "0" jobs at first run'
        );

        // add job
        $this->assertTrue($fileSystemRepository->addJob($entity));
        $this->assertTrue(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . 'JobX.json'));

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($jobs),
            'Expected "1" job after adding'
        );

        foreach ($jobs as $job) {
            $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', $job);
            $this->assertInstanceOf('Chapi\Entity\Chronos\ChronosJobEntity', $job);
        }

        // update job
        $entity->disabled = true;
        $entity->mem = 123;

        $this->assertTrue($fileSystemRepository->updateJob($entity));

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($jobs),
            'Expected still "1" job after update'
        );

        $this->assertEquals(123, $jobs[0]->mem);
        $this->assertTrue($jobs[0]->disabled);

        // remove job
        $this->assertTrue($fileSystemRepository->removeJob($entity));
        $this->assertFalse(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . 'JobX.json'));

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($jobs),
            'Expected "0" jobs after deletion'
        );
    }

    public function testAddUpdateRemoveMarathonAppEntitySuccess()
    {
        $fileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $fileSystemRepository = new BridgeFileSystem(
            $fileSystemService,
            $this->cache->reveal(),
            $this->tempTestDir
        );

        $appEntity = $this->getValidMarathonAppEntity('/testgroup/testapp');

        // first check and init
        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($jobs),
            'Expected "0" app at first run'
        );

        // add app
        $this->assertTrue($fileSystemRepository->addJob($appEntity));
        $this->assertTrue(file_exists($this->tempTestDir. DIRECTORY_SEPARATOR. 'testgroup'. DIRECTORY_SEPARATOR .'testapp.json'));

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($jobs),
            'Expected "1" app after adding'
        );

        foreach ($jobs as $job) {
            $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', $job);
            $this->assertInstanceOf('Chapi\Entity\Marathon\MarathonAppEntity', $job);
        }

        // update app
        $appEntity->cpus = 2;
        $appEntity->mem = 1024;

        $this->assertTrue($fileSystemRepository->updateJob($appEntity));

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            1,
            count($jobs),
            'Expected still "1" job after update'
        );

        $this->assertEquals(2, $jobs[0]->cpus);
        $this->assertEquals(1024, $jobs[0]->mem);

        // remove job
        $this->assertTrue($fileSystemRepository->removeJob($appEntity));
        $this->assertFalse(file_exists($this->tempTestDir . DIRECTORY_SEPARATOR . 'testgroup'. DIRECTORY_SEPARATOR .'testapp.json'));

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(
            0,
            count($jobs),
            'Expected "0" jobs after deletion'
        );
    }

    public function testUpdateMarathonAppEntityInGroupSuccess()
    {

        $structure = array(
            'externalGroup' => array(
                'testgroup.json' => json_encode($this->getValidMarathonAppEntityGroup("/externalGroup/testgroup"))
            )
        );

        vfsStream::setup($this->repositoryDir, null, $structure);
        $fileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $fileSystemRepository = new BridgeFileSystem(
            $fileSystemService,
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir)
        );

        $apps = $fileSystemRepository->getJobs();
        $this->assertEquals(2, count($apps), "Expected 2 app, got ".count($apps));

        $updatedKey = $apps[0]->getKey();
        $apps[0]->mem = 1024;
        $apps[0]->cpus = 2;

        $this->assertTrue($fileSystemRepository->updateJob($apps[0]), 'UpdateJob returned false, expected true');

        $appsAfterModification = $fileSystemRepository->getJobs();
        $this->assertEquals(2, count($apps), "Expected 2 apps, got " . count($appsAfterModification));

        foreach ($appsAfterModification as $app) {
            if ($app->getKey() == $updatedKey) {
                $this->assertEquals(
                    1024,
                    $app->mem,
                    "Expected 1024, got $app->mem"
                );

                $this->assertEquals(
                    2,
                    $app->cpus,
                    "Expected 2, got $app->cpus"
                );
            }
        }
    }


    public function testRemoveMarathonAppEntityInGroupSuccess()
    {
        $groupConfig = $this->getValidMarathonAppEntityGroup("/externalGroup/testgroup");
        $structure = array(
            'externalGroup' => array(
                'testgroup.json' => json_encode($groupConfig)
            )
        );

        vfsStream::setup($this->repositoryDir, null, $structure);
        $fileSystemService = new \Symfony\Component\Filesystem\Filesystem();

        $fileSystemRepository = new BridgeFileSystem(
            $fileSystemService,
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir)
        );

        $jobs = $fileSystemRepository->getJobs();
        $this->assertEquals(2, count($jobs), 'Expected 2 app before removal, found '.count($jobs));

        $fileSystemRepository->removeJob($groupConfig["apps"][0]);

        $remainingApps = $fileSystemRepository->getJobs();

        $this->assertEquals(1, count($remainingApps), 'Expected 1 app remaining after removal, found ' . count($remainingApps));
    }


    /**
     * @expectedException \Chapi\Exception\JobLoadException
     */
    public function testJobLoadException()
    {
        $structure = array(
            'directory' => array(
                'jobA.json' => 'no-json-string',
            ),
            'jobB.json' => '{invalid-json: true',
        );

        $this->vfsRoot = vfsStream::setup($this->repositoryDir, null, $structure);

        $fileSystemRepository = new BridgeFileSystem(
            $this->fileSystemService->reveal(),
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir)
        );

        $jobs = $fileSystemRepository->getJobs();
        $this->assertNull($jobs);
    }

    /**
     * @expectedException \Chapi\Exception\JobLoadException
     */
    public function testJobLoadExceptionForDuplicateJobNames()
    {
        $structure = array(
            'directory' => array(
                'jobA.json' => json_encode($this->getValidScheduledJobEntity('JobA')),
            ),
            'jobB.json' => json_encode($this->getValidScheduledJobEntity('JobA')),
        );

        $this->vfsRoot = vfsStream::setup($this->repositoryDir, null, $structure);

        $fileSystemRepository = new BridgeFileSystem(
            $this->fileSystemService->reveal(),
            $this->cache->reveal(),
            vfsStream::url($this->repositoryDir)
        );

        $jobs = $fileSystemRepository->getJobs();
        $this->assertNull($jobs);
    }
}
