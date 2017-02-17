<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 */


namespace Chapi\Component\Command;


class CommandUtils implements CommandUtilsInterface
{
    /**
     * @link   https://github.com/composer/composer/blob/69210d5bc130f8cc9f96f99582a041254d7b9833/src/Composer/Factory.php
     * @return string
     */
    public static function getOsHomeDir()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR'))
        {
            if (!getenv('APPDATA'))
            {
                throw new \RuntimeException('The APPDATA or CHAPI_HOME environment variable must be set for composer to run correctly');
            }
            return  rtrim(
                strtr(getenv('APPDATA'), '\\', '/'),
                '/'
            );
        }

        // else
        if (!getenv('HOME'))
        {
            throw new \RuntimeException('The HOME or CHAPI_HOME environment variable must be set for composer to run correctly');
        }
        return rtrim(getenv('HOME'), '/');
    }

    /**
     * @param string $sDir
     * @return bool
     */
    public static function hasCreateDirectoryIfNotExists($sDir)
    {
        if (!is_dir($sDir))
        {
            if (!mkdir($sDir, 0755, true))
            {
                throw new \RuntimeException(sprintf('Unable to create cache directory "%s"', $sDir));
            }
        }

        return true;
    }
}