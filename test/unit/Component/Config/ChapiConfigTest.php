<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-22
 *
 */

namespace unit\Component\Config;

use Chapi\Component\Config\ChapiConfig;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class ChapiConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  vfsStreamDirectory */
    private $oVfsRoot;

    /** @var string  */
    private $sTempTestDir = '';

    /** @var string */
    private $sRepositoryDir = 'FilterIgnoreSettingsTestDir';

    /**
     * @var Parser
     */
    private $oParser;

    /**
     * @var Dumper
     */
    private $oDumper;

    /**
     * @var array
     */
    private $aTestConfigA;

    public function setUp()
    {
        $this->oParser = new Parser();
        $this->oDumper = new Dumper();

        $this->aTestConfigA = [
            'profiles' => [
                'default' => [
                    'parameters' => [
                        'cache_dir' => '/tmp/cache/dir',
                        'chronos_url' => 'http://chronos.url:4400/',
                        'chronos_http_username' => 'user',
                        'chronos_http_password' => 'pass',
                        'repository_dir' => '/tmp/chronos/repository',
                        'marathon_url' => null,
                        'marathon_http_username' => null,
                        'marathon_http_password' => null,
                        'repository_dir_marathon' => null,
                    ],
                    'ignore' => ['*-stage']
                ]
            ]
        ];

        $_sConfig = $this->oDumper->dump($this->aTestConfigA);

        $_aStructure = array(
            '.chapiconfig' => $_sConfig
        );
        $this->oVfsRoot = vfsStream::setup($this->sRepositoryDir, null, $_aStructure);
    }

    public function testGetConfig()
    {
        $_oChapiConfig = new ChapiConfig(
            [vfsStream::url($this->sRepositoryDir)],
            $this->oParser,
            'default'
        );

        $_aConfig = $_oChapiConfig->getConfig();

        $this->assertArrayHasKey('profiles', $_aConfig);
        $this->assertEquals($this->aTestConfigA, $_aConfig);
    }

    public function testGetProfileConfig()
    {
        $_oChapiConfig = new ChapiConfig(
            [vfsStream::url($this->sRepositoryDir)],
            $this->oParser,
            'default'
        );

        $_aProfileConfig = $_oChapiConfig->getProfileConfig();

        $this->assertArrayNotHasKey('profiles', $_aProfileConfig);
        $this->assertArrayHasKey('parameters', $_aProfileConfig);
        $this->assertEquals($this->aTestConfigA['profiles']['default'], $_aProfileConfig);
    }

    public function testConfigMerge()
    {
        $_aTestConfigB = [
            'profiles' => [
                'default' => [
                    'parameters' => [
                        'cache_dir' => '/tmp/cache/update',
                        'chronos_http_username' => 'update_user',
                        'marathon_http_username' => 'new_user'
                    ],
                    'ignore' => ['new-entry']
                ],
                'second_profile' => $this->aTestConfigA['profiles']['default']
            ]
        ];

        $_aStructure = array(
            'directory' => array(
                '.chapiconfig' => $this->oDumper->dump($_aTestConfigB)
            ),
            '.chapiconfig' => $this->oDumper->dump($this->aTestConfigA)

        );

        $this->oVfsRoot = vfsStream::setup($this->sRepositoryDir, null, $_aStructure);

        $_oChapiConfig = new ChapiConfig(
            [
                vfsStream::url($this->sRepositoryDir),
                vfsStream::url($this->sRepositoryDir) . DIRECTORY_SEPARATOR . 'directory'
            ],
            $this->oParser,
            'default'
        );

        $_aConfig = $_oChapiConfig->getConfig();

        $this->assertArrayHasKey('profiles', $_aConfig);
        $this->assertArrayHasKey('default', $_aConfig['profiles']);
        $this->assertArrayHasKey('second_profile', $_aConfig['profiles']);
        $this->assertNotEquals($this->aTestConfigA, $_aConfig);

        $_aProfileConfig = $_oChapiConfig->getProfileConfig();

        $this->assertEquals('/tmp/cache/update', $_aProfileConfig['parameters']['cache_dir']);
        $this->assertEquals('update_user', $_aProfileConfig['parameters']['chronos_http_username']);
        $this->assertEquals('new_user', $_aProfileConfig['parameters']['marathon_http_username']);

        $this->assertArraySubset(['*-stage', 'new-entry'], $_aProfileConfig['ignore']);
    }
}
