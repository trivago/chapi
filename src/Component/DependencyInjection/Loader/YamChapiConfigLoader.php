<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-20
 *
 */

namespace Chapi\Component\DependencyInjection\Loader;


use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

class YamChapiConfigLoader extends \Symfony\Component\DependencyInjection\Loader\YamlFileLoader
{
    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * @inheritdoc
     */
    public function loadProfileParameters($sResource, $sProfile)
    {
        $path = $this->locator->locate($sResource);

        $content = $this->loadFile($path);

        $this->container->addResource(new FileResource($path));

        // empty file
        if (null === $content) {
            return;
        }

        // parameters
        if (isset($content['profiles']) && isset($content['profiles'][$sProfile]) && isset($content['profiles'][$sProfile]['parameters'])) {
            if (!is_array($content['profiles'][$sProfile]['parameters'])) {
                throw new InvalidArgumentException(sprintf('The "parameters" key should contain an array in %s. Check your YAML syntax.', $sResource));
            }

            foreach ($content['profiles'][$sProfile]['parameters'] as $key => $value) {
                $this->container->setParameter($key, $value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function loadFile($file)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new RuntimeException('Unable to load YAML config files as the Symfony Yaml Component is not installed.');
        }

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $configuration = $this->yamlParser->parse(file_get_contents($file), Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $file), 0, $e);
        }

        return $this->validate($configuration, $file);
    }

    /**
     * @inheritdoc
     */
    private function validate($content, $file)
    {
        if (null === $content) {
            return $content;
        }

        if (!is_array($content)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid. It should contain an array. Check your YAML syntax.', $file));
        }

        foreach ($content as $namespace => $data) {
            if (in_array($namespace, array('profiles'))) {
                continue;
            }

            if (!$this->container->hasExtension($namespace)) {
                $extensionNamespaces = array_filter(array_map(function ($ext) { return $ext->getAlias(); }, $this->container->getExtensions()));
                throw new InvalidArgumentException(sprintf(
                    'There is no extension able to load the configuration for "%s" (in %s). Looked for namespace "%s", found %s',
                    $namespace,
                    $file,
                    $namespace,
                    $extensionNamespaces ? sprintf('"%s"', implode('", "', $extensionNamespaces)) : 'none'
                ));
            }
        }

        return $content;
    }
}