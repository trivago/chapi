<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-02
 *
 */

namespace ChapiTest\src\TestTraits;

use Prophecy\Argument;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

trait CommandTestTrait
{
    protected $oInput;

    protected $oOutput;

    protected $oContainer;

    protected function setUpCommandDependencies()
    {
        $this->oInput = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $this->oInput->bind(Argument::any())->willReturn(null);
        $this->oInput->isInteractive()->willReturn(true);
        $this->oInput->validate()->willReturn(null);
        // after symfony console v2.7.5
        $this->oInput->hasArgument('command')->willReturn(true);
        $this->oInput->getArgument('command')->willReturn('testCommand');

        // global profile option from Chapi\Commands\AbstractCommand
        $this->oInput->getOption('profile')->willReturn(null);

        $this->oOutput = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->oOutput->writeln(Argument::type('string'))->willReturn(null);
        $this->oOutput->write(Argument::type('string'))->willReturn(null);
        $this->oOutput->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $this->oOutput->getFormatter()->willReturn(new OutputFormatter());

        $_ConsoleHandler = $this->prophesize('\Symfony\Bridge\Monolog\Handler\ConsoleHandler');

        $this->oContainer = $this->prophesize('Symfony\Component\DependencyInjection\TaggedContainerInterface');
        $this->oContainer->get(Argument::exact('ConsoleHandler'))->willReturn($_ConsoleHandler->reveal());
    }
}
