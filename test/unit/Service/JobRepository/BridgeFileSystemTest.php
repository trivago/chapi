<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-21
 */


namespace unit\Service\JobRepository;

use Chapi\Service\JobRepository\BridgeFileSystem;
use ChapiTest\src\TestTraits\JobEntityTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

class BridgeFileSystemTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

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

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobEntity', $_aJobs[0]);
    }

    public function testAddUpdateRemoveJobSuccess()
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

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobEntity', $_aJobs[0]);

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
}