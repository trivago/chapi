<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 */


namespace unit\Component\Command;

use Chapi\Component\Command\CommandUtils;
use org\bovigo\vfs\vfsStream;

class CommandUtilsTest extends \PHPUnit\Framework\TestCase
{


    public function testGetOsHomeDirUnix()
    {
        putenv('HOME=/var/tmp/');

        $this->assertEquals('/var/tmp', CommandUtils::getOsHomeDir());
    }

    public function testGetOsHomeDirWindows()
    {
        putenv('APPDATA=c:\user\directory\\');
        define('PHP_WINDOWS_VERSION_MAJOR', '8.1');

        $this->assertEquals('c:/user/directory', CommandUtils::getOsHomeDir());
    }

    public function testHasCreateDirectoryIfNotExists()
    {
        vfsStream::setup('root');

        $this->assertTrue(
            CommandUtils::hasCreateDirectoryIfNotExists(
                vfsStream::url('root/directory')
            )
        );

        $this->assertTrue(is_dir(vfsStream::url('root/directory')));
    }
}
