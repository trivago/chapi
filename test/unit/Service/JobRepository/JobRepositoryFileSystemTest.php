<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-21
 */


namespace unit\Service\JobRepository;


use Chapi\Service\JobRepository\JobRepositoryFileSystem;

class JobRepositoryFileSystemTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oFileSystemService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oCache;

    /** @var string */
    private $sRepositoryDir = '/var/test/';

    public function setUp()
    {
        $this->oFileSystemService = $this->prophesize('Symfony\Component\Filesystem\Filesystem');

        $this->oCache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
    }

    public function testCreateInstance()
    {
        $_oFileSystemRepository = new JobRepositoryFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            $this->sRepositoryDir
        );

        $this->assertInstanceOf('Chapi\Service\JobRepository\JobRepositoryServiceInterface', $_oFileSystemRepository);
    }
}