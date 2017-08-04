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
    protected $input;

    protected $output;

    protected $container;

    protected function setUpCommandDependencies()
    {
        $this->input = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $this->input->bind(Argument::any())->willReturn(null);
        $this->input->isInteractive()->willReturn(true);
        $this->input->validate()->willReturn(null);
        // after symfony console v2.7.5
        $this->input->hasArgument('command')->willReturn(true);
        $this->input->getArgument('command')->willReturn('testCommand');

        // global profile option from Chapi\Commands\AbstractCommand
        $this->input->getOption('profile')->willReturn(null);

        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->output->writeln(Argument::type('string'))->willReturn(null);
        $this->output->write(Argument::type('string'))->willReturn(null);
        $this->output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $this->output->getFormatter()->willReturn(new OutputFormatter());

        $_ConsoleHandler = $this->prophesize('\Symfony\Bridge\Monolog\Handler\ConsoleHandler');

        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\TaggedContainerInterface');
        $this->container->get(Argument::exact('ConsoleHandler'))->willReturn($_ConsoleHandler->reveal());
    }
}
