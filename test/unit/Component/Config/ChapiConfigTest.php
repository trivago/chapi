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

class ChapiConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var  vfsStreamDirectory */
    private $vfsRoot;

    /** @var string  */
    private $tempTestDir = '';

    /** @var string */
    private $repositoryDir = 'FilterIgnoreSettingsTestDir';

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Dumper
     */
    private $dumper;

    /**
     * @var array
     */
    private $testConfigA;

    protected function setUp(): void
    {
        $this->parser = new Parser();
        $this->dumper = new Dumper();

        $this->testConfigA = [
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

        $config = $this->dumper->dump($this->testConfigA);

        $structure = array(
            '.chapiconfig' => $config
        );
        $this->vfsRoot = vfsStream::setup($this->repositoryDir, null, $structure);
    }

    public function testGetConfig()
    {
        $chapiConfig = new ChapiConfig(
            [vfsStream::url($this->repositoryDir)],
            $this->parser,
            'default'
        );

        $config = $chapiConfig->getConfig();

        $this->assertArrayHasKey('profiles', $config);
        $this->assertEquals($this->testConfigA, $config);
    }

    public function testGetProfileConfig()
    {
        $chapiConfig = new ChapiConfig(
            [vfsStream::url($this->repositoryDir)],
            $this->parser,
            'default'
        );

        $profileConfig = $chapiConfig->getProfileConfig();

        $this->assertArrayNotHasKey('profiles', $profileConfig);
        $this->assertArrayHasKey('parameters', $profileConfig);
        $this->assertEquals($this->testConfigA['profiles']['default'], $profileConfig);
    }

    public function testConfigMerge()
    {
        $testConfigB = [
            'profiles' => [
                'default' => [
                    'parameters' => [
                        'cache_dir' => '/tmp/cache/update',
                        'chronos_http_username' => 'update_user',
                        'marathon_http_username' => 'new_user'
                    ],
                    'ignore' => ['new-entry']
                ],
                'second_profile' => $this->testConfigA['profiles']['default']
            ]
        ];

        $structure = array(
            'directory' => array(
                '.chapiconfig' => $this->dumper->dump($testConfigB)
            ),
            '.chapiconfig' => $this->dumper->dump($this->testConfigA)

        );

        $this->vfsRoot = vfsStream::setup($this->repositoryDir, null, $structure);

        $chapiConfig = new ChapiConfig(
            [
                vfsStream::url($this->repositoryDir),
                vfsStream::url($this->repositoryDir) . DIRECTORY_SEPARATOR . 'directory'
            ],
            $this->parser,
            'default'
        );

        $config = $chapiConfig->getConfig();

        $this->assertArrayHasKey('profiles', $config);
        $this->assertArrayHasKey('default', $config['profiles']);
        $this->assertArrayHasKey('second_profile', $config['profiles']);
        $this->assertNotEquals($this->testConfigA, $config);

        $profileConfig = $chapiConfig->getProfileConfig();

        $this->assertSame('/tmp/cache/update', $profileConfig['parameters']['cache_dir']);
        $this->assertSame('update_user', $profileConfig['parameters']['chronos_http_username']);
        $this->assertSame('new_user', $profileConfig['parameters']['marathon_http_username']);

        $this->assertEquals(['*-stage', 'new-entry'], $profileConfig['ignore']);
    }
}
