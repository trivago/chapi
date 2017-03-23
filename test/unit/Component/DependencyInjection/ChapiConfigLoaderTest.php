<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-23
 *
 */

namespace unit\Component\Config;

use Chapi\Component\DependencyInjection\Loader\ChapiConfigLoader;

class ChapiConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oContainer;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oConfig;

    public function setUp()
    {
        // Symfony\Component\DependencyInjection\ContainerInterface
        $this->oContainer = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->oConfig = $this->prophesize('Chapi\Component\Config\ChapiConfigInterface');
    }

    public function testLoadProfileParametersWithoutConfigSettings() {
        $_oChapiConfigLoader = new ChapiConfigLoader(
            $this->oContainer->reveal(),
            $this->oConfig->reveal()
        );

        $this->assertNull($_oChapiConfigLoader->loadProfileParameters());
    }

    public function testLoadProfileParametersWithConfigSettings() {
        $this->oConfig->getProfileConfig()->willReturn([
            'parameters' => [
                'paramA' => 'A',
                'paramB' => 'B'
            ]
        ]);

        $_oChapiConfigLoader = new ChapiConfigLoader(
            $this->oContainer->reveal(),
            $this->oConfig->reveal()
        );

        $this->assertNull($_oChapiConfigLoader->loadProfileParameters());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadProfileParametersFailure() {
        $this->oConfig->getProfileConfig()->willReturn([
            'parameters' => 'not_valid'
        ]);

        $_oChapiConfigLoader = new ChapiConfigLoader(
            $this->oContainer->reveal(),
            $this->oConfig->reveal()
        );

        $_oChapiConfigLoader->loadProfileParameters();
    }
}