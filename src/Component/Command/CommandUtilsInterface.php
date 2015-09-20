<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 */


namespace Chapi\Component\Command;


interface CommandUtilsInterface
{
    /**
     * @return string
     */
    public static function getOsHomeDir();

    /**
     * @param string $sDir
     * @return bool
     */
    public static function hasCreateDirectoryIfNotExists($sDir);
}