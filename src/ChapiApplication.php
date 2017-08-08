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
    public function __construct($name = 'Chapi', $version = '@package_version@')
    {
        if ('@package_version@' !== $version) {
            $version = ltrim($version, 'v');
        }

        parent::__construct($name, $version);

        $this->setDefaultCommand('help.commands');
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
            new Commands\HelpCommandsCommand(),
            new Commands\InfoCommand(),
            new Commands\ListCommand(),
            new Commands\PullCommand(),
            new Commands\ResetCommand(),
            new Commands\SchedulingViewCommand(),
            new Commands\StatusCommand(),
            new Commands\ValidationCommand(),
        ];
    }
}
