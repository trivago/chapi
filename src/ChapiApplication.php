<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */
namespace Chapi;

use Symfony\Component\Console\Application;

class ChapiApplication extends Application
{
    public function __construct($sName = 'trivago chapi', $sVersion = '@package_version@')
    {
        if ('@package_version@' !== $sVersion) {
            $sVersion = ltrim($sVersion, 'v');
        }

        parent::__construct($sName, $sVersion);

        $this->addCommands($this->getCommands());
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    private function getCommands()
    {
        return [
            // GENERAL COMMANDS
            new Commands\AddCommand(),
            new Commands\CommitCommand(),
            new Commands\ConfigureCommand(),
            new Commands\DiffCommand(),
            new Commands\InfoCommand(),
            new Commands\JobsCommand(),
            new Commands\ResetCommand(),
            new Commands\StatusCommand(),
        ];
    }
}