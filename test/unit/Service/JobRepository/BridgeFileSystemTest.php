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

class BridgeFileSystemTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
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

    private function rmrf($path) {
        foreach (glob($path."/*", GLOB_MARK) as $file) {
            if (is_dir($file)) {
                $this->rmrf($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }

    protected function tearDown(): void
    {
        $this->rmrf($this->tempTestDir);
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
        $this->assertCount(
            4,
            $jobs
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
        $this->assertCount(
            2,
            $entities
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

        $this->assertSame(1, $countChronos, "Expected 1 chronos job, got $countChronos");
        $this->assertSame(1, $countMarathon, "Expected 1 marathon app, got $countMarathon");
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
        $this->assertCount(
            0,
            $jobs,
            'Expected "0" jobs at first run'
        );

        // add job
        $this->assertTrue($fileSystemRepository->addJob($entity));
        $this->assertFileExists($this->tempTestDir . DIRECTORY_SEPARATOR . 'JobX.json');

        $jobs = $fileSystemRepository->getJobs();
        $this->assertCount(
            1,
            $jobs,
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
        $this->assertCount(
            1,
            $jobs,
            'Expected still "1" job after update'
        );

        $this->assertSame(123, $jobs[0]->mem);
        $this->assertTrue($jobs[0]->disabled);

        // remove job
        $this->assertTrue($fileSystemRepository->removeJob($entity));
        $this->assertFileNotExists($this->tempTestDir . DIRECTORY_SEPARATOR . 'JobX.json');

        $jobs = $fileSystemRepository->getJobs();
        $this->assertCount(
            0,
            $jobs,
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
        $this->assertCount(
            0,
            $jobs,
            'Expected "0" app at first run'
        );

        // add app
        $this->assertTrue($fileSystemRepository->addJob($appEntity));
        $this->assertFileExists($this->tempTestDir. DIRECTORY_SEPARATOR. 'testgroup'. DIRECTORY_SEPARATOR .'testapp.json');

        $jobs = $fileSystemRepository->getJobs();
        $this->assertCount(
            1,
            $jobs,
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
        $this->assertCount(
            1,
            $jobs,
            'Expected still "1" job after update'
        );

        $this->assertSame(2, $jobs[0]->cpus);
        $this->assertSame(1024, $jobs[0]->mem);

        // remove job
        $this->assertTrue($fileSystemRepository->removeJob($appEntity));
        $this->assertFileNotExists($this->tempTestDir . DIRECTORY_SEPARATOR . 'testgroup'. DIRECTORY_SEPARATOR .'testapp.json');

        $jobs = $fileSystemRepository->getJobs();
        $this->assertCount(
            0,
            $jobs,
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
        $this->assertCount(2, $apps, "Expected 2 app, got ".count($apps));

        $updatedKey = $apps[0]->getKey();
        $apps[0]->mem = 1024;
        $apps[0]->cpus = 2;

        $this->assertTrue($fileSystemRepository->updateJob($apps[0]), 'UpdateJob returned false, expected true');

        $appsAfterModification = $fileSystemRepository->getJobs();
        $this->assertCount(2, $apps, "Expected 2 apps, got " . count($appsAfterModification));

        foreach ($appsAfterModification as $app) {
            if ($app->getKey() == $updatedKey) {
                $this->assertSame(
                    1024,
                    $app->mem,
                    "Expected 1024, got $app->mem"
                );

                $this->assertSame(
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
        $this->assertCount(2, $jobs, 'Expected 2 app before removal, found '.count($jobs));

        $fileSystemRepository->removeJob($groupConfig["apps"][0]);

        $remainingApps = $fileSystemRepository->getJobs();

        $this->assertCount(1, $remainingApps, 'Expected 1 app remaining after removal, found ' . count($remainingApps));
    }

    public function testJobLoadException()
    {
        $this->expectException(\Chapi\Exception\JobLoadException::class);

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

    public function testJobLoadExceptionForDuplicateJobNames()
    {
        $this->expectException(\Chapi\Exception\JobLoadException::class);

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
