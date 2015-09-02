<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-02
 */

namespace Chapi\Component\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

interface JobUtilsInterface
{
    const ARGUMENT_JOBNAMES = 'jobmames';

    /**
     * @param Command $oCommand
     * @param string $sDescription
     * @return void
     */
    public static function configureJobNamesArgument(Command $oCommand, $sDescription);

    /**
     * @param InputInterface $oInput
     * @param Command $oCommand
     * @return string[]
     */
    public static function getJobNames(InputInterface $oInput, Command $oCommand);

    /**
     * @param string[] $aJobNames
     * @return bool
     */
    public static function isWildcard(array $aJobNames);
}