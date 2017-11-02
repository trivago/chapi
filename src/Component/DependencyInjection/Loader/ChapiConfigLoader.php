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
use \InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ChapiConfigLoader implements ChapiConfigLoaderInterface
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var ChapiConfigInterface
     */
    private $config;

    /**
     * ChapiConfigLoader constructor.
     * @param ContainerBuilder $container
     * @param ChapiConfigInterface $config
     */
    public function __construct(ContainerBuilder $container, ChapiConfigInterface $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function loadProfileParameters()
    {
        $content = $this->config->getProfileConfig();

        $this->container->addObjectResource($this->config);

        // empty config
        if (empty($content)) {
            return;
        }

        // parameters
        if (isset($content['parameters'])) {
            foreach ($content['parameters'] as &$parameter) {
                if ($parameter === null || $parameter === 'null') {
                    $parameter = '';
                }
            }

            $this->validate($content);
            $this->setParameters($content['parameters']);
        }
    }

    /**
     * @param array $content
     * @throws InvalidArgumentException
     * @return void
     */
    private function validate(array &$content)
    {
        if (isset($content['parameters']) && !is_array($content['parameters'])) {
            throw new InvalidArgumentException('The "parameters" key should contain an array. Please check your configuration files.');
        }
    }

    /**
     * @param array $parameters
     */
    private function setParameters(array &$parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->container->setParameter($key, $value);
        }
    }
}
