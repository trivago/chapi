<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-23
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/18
 */

namespace Chapi\Entity\Logger;

use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

class VerbosityLevelMap
{
    /**
     * @var array
     */
    public static $verbosityLevelMap = array(
        OutputInterface::VERBOSITY_NORMAL => Logger::NOTICE,
        OutputInterface::VERBOSITY_VERBOSE => Logger::INFO,
        OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
        OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
    );

    /**
     * @return array
     */
    public static function getVerbosityLevelMap()
    {
        return self::$verbosityLevelMap;
    }
}
