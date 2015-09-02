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
    public static function configureJobNamesArgument(Command $oCommand, $sDescription)
    {
        $oCommand->addArgument(self::ARGUMENT_JOBNAMES, InputArgument::IS_ARRAY, $sDescription);
    }

    /**
     * @inheritdoc
     */
    public static function getJobNames(InputInterface $oInput, Command $oCommand)
    {
        $_aJobNames = $oInput->getArgument(self::ARGUMENT_JOBNAMES);

        if (empty($_aJobNames))
        {
            throw new \InvalidArgumentException(sprintf('Nothing specified, nothing %sed. Maybe you wanted to say "%s ."?', $oCommand->getName(), $oCommand->getName()));
        }

        return $_aJobNames;
    }

    /**
     * @inheritdoc
     */
    public static function isWildcard(array $aJobNames)
    {
        return (isset($aJobNames[0]) && in_array($aJobNames[0], ['.', '*']));
    }
}