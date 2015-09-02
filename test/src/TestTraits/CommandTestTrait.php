<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-02
 *
 */

namespace ChapiTest\src\TestTraits;

use Chapi\Component\Command\JobUtilsInterface;
use Prophecy\Argument;
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

        $this->oOutput = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->oOutput->writeln(Argument::type('string'))->willReturn(null);
        $this->oOutput->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);

        $_ConsoleHandler = $this->prophesize('\Symfony\Bridge\Monolog\Handler\ConsoleHandler');

        $this->oContainer = $this->prophesize('Symfony\Component\DependencyInjection\TaggedContainerInterface');
        $this->oContainer->get(Argument::exact('ConsoleHandler'))->willReturn($_ConsoleHandler->reveal());
    }
}