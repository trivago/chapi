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
     * @param Command $command
     * @param string $description
     * @return void
     */
    public static function configureJobNamesArgument(Command $command, $description);

    /**
     * @param InputInterface $input
     * @param Command $command
     * @return string[]
     */
    public static function getJobNames(InputInterface $input, Command $command);

    /**
     * @param string[] $jobNames
     * @return bool
     */
    public static function isWildcard(array $jobNames);
}
