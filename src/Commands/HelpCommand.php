<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-02
 *
 */


namespace Chapi\Commands;


use Symfony\Component\Console\Command\ListCommand;

class HelpCommand extends ListCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('help');
    }
}