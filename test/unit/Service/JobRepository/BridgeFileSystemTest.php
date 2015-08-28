<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-21
 */


namespace unit\Service\JobRepository;


use Chapi\Service\JobRepository\BridgeFileSystem;

class BridgeFileSystemTest extends \PHPUnit_Framework_TestCase
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
        $_oFileSystemRepository = new BridgeFileSystem(
            $this->oFileSystemService->reveal(),
            $this->oCache->reveal(),
            $this->sRepositoryDir
        );

        $this->assertInstanceOf('Chapi\Service\JobRepository\BridgeFileSystem', $_oFileSystemRepository);
    }
}