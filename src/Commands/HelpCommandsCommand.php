<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-02
 *
 */


namespace Chapi\Commands;

use Symfony\Component\Console\Command\ListCommand as SymfonyListCommand;

class HelpCommandsCommand extends SymfonyListCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('help.commands');
    }
}
