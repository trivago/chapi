<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-02
 */

namespace Chapi\Component\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class JobUtils implements JobUtilsInterface
{
    /**
     * @inheritdoc
     */
    public static function configureJobNamesArgument(Command $command, $description)
    {
        $command->addArgument(self::ARGUMENT_JOBNAMES, InputArgument::IS_ARRAY, $description);
    }

    /**
     * @inheritdoc
     */
    public static function getJobNames(InputInterface $input, Command $command)
    {
        $jobNames = $input->getArgument(self::ARGUMENT_JOBNAMES);

        if (empty($jobNames)) {
            throw new \InvalidArgumentException(sprintf('Nothing specified, nothing %sed. Maybe you wanted to say "%s ."?', $command->getName(), $command->getName()));
        }

        return $jobNames;
    }

    /**
     * @inheritdoc
     */
    public static function isWildcard(array $jobNames)
    {
        return (isset($jobNames[0]) && in_array($jobNames[0], ['.', '*']));
    }
}
