<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-22
 *
 */

namespace Chapi\Component\Config;

use Symfony\Component\Yaml\Parser as YamlParser;

class ChapiConfig implements ChapiConfigInterface
{
    /**
     * @var string[]
     */
    private $directoryPaths = [];

    /**
     * @var YamlParser
     */
    private $parser;

    /**
     * @var string
     */
    private $activeProfile = '';

    /**
     * @var array
     */
    private $config = null;

    /**
     * ChapiConfig constructor.
     * @param string[] $directoryPaths
     * @param YamlParser $parser
     * @param string $activeProfile
     */
    public function __construct(
        $directoryPaths,
        YamlParser $parser,
        $activeProfile
    ) {
        $this->directoryPaths = $directoryPaths;
        $this->parser = $parser;
        $this->activeProfile = $activeProfile;
    }

    /**
     * @inheritdoc
     */
    public function getProfileConfig()
    {
        $config = $this->getConfig();
        return $config['profiles'][$this->activeProfile];
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->loadConfigs();
        }

        return $this->config;
    }

    /**
     * @return array
     */
    private function loadConfigs()
    {
        $config = [];

        foreach ($this->directoryPaths as $directoryPath) {
            if (!is_dir($directoryPath)) {
                throw new \InvalidArgumentException(sprintf('Path "%s" is not valid', $directoryPath));
            }

            $configPart = $this->loadConfig($directoryPath);
            $config = self::arrayMergeRecursiveDistinct($config, $configPart);
        }

        return $config;
    }

    /**
     * @param string $path
     * @return array
     */
    private function loadConfig($path)
    {
        $filePath = $path . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;

        if (is_file($filePath)) {
            $config = $this->parser->parse(
                file_get_contents($filePath)
            );

            return $config;
        }

        return [];
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     *
     * @see http://php.net/manual/de/function.array-merge-recursive.php
     */
    private static function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                // add new element for numeric arrays
                if (isset($merged[$key]) && is_numeric($key)) {
                    $merged[] = $value;
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }
}
