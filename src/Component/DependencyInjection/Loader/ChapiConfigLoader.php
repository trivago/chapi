<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-20
 *
 */

namespace Chapi\Component\DependencyInjection\Loader;


use Chapi\Component\Config\ChapiConfigInterface;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ChapiConfigLoader implements ChapiConfigLoaderInterface
{
    /**
     * @var ContainerBuilder
     */
    private $oContainer;

    /**
     * @var ChapiConfigInterface
     */
    private $oConfig;

    /**
     * ChapiConfigLoader constructor.
     * @param ContainerBuilder $oContainer
     * @param ChapiConfigInterface $oConfig
     */
    public function __construct(ContainerBuilder $oContainer, ChapiConfigInterface $oConfig)
    {
        $this->oContainer = $oContainer;
        $this->oConfig = $oConfig;
    }

    /**
     * @inheritdoc
     */
    public function loadProfileParameters()
    {
        $_aContent = $this->oConfig->getProfileConfig();

        $this->oContainer->addObjectResource($this->oConfig);

        // empty config
        if (empty($_aContent)) {
            return;
        }

        // parameters
        if (isset($_aContent['parameters'])) {
            $this->setParameters($_aContent['parameters']);
        }
    }

    /**
     * @param array $aParameters
     */
    private function setParameters(array &$aParameters)
    {
        if (!is_array($aParameters)) {
            throw new InvalidArgumentException('The "parameters" key should contain an array. Please check your configuration files.');
        }

        foreach ($aParameters as $key => $value) {
            $this->oContainer->setParameter($key, $value);
        }
    }
}