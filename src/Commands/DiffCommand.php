<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-30
 *
 */

namespace Chapi\Commands;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Symfony\Component\Console\Input\InputArgument;

class DiffCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diff')
            ->setDescription('Show changes between jobs and working tree, etc')
            ->addArgument('jobName', InputArgument::OPTIONAL, 'Show changes for specific job')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var JobComparisonInterface  $_oJobComparisonBusinessCase */
        $_oJobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);
        $_sJobName = $this->oInput->getArgument('jobName');

        if (!empty($_sJobName)) {
            $this->printJobDiff($_sJobName);
        } else {
            $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();
            if (!empty($_aLocalJobUpdates)) {
                foreach ($_aLocalJobUpdates as $_sJobName) {
                    $this->printJobDiff($_sJobName);
                }
            }
        }

        return 0;
    }

    /**
     * @param $sJobName
     */
    private function printJobDiff($sJobName)
    {
        /** @var JobComparisonInterface  $_oJobComparisonBusinessCase */
        $_oJobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        $this->oOutput->writeln(sprintf("\n<comment>diff %s</comment>", $sJobName));

        $_aJobDiff = $_oJobComparisonBusinessCase->getJobDiff($sJobName);

        foreach ($_aJobDiff as $_sProperty => $_sDiff) {
            $_aDiffLines = array_reverse(explode(PHP_EOL, $_sDiff));

            foreach ($_aDiffLines as $_sDiffLine) {
                $_sDiffSign = substr($_sDiffLine, 0, 1);

                if ($_sDiffSign == '+') {
                    $this->oOutput->writeln(sprintf("<info>%s\t%s: %s</info>", $_sDiffSign, $_sProperty, substr($_sDiffLine, 1)));
                } elseif ($_sDiffSign == '-') {
                    $this->oOutput->writeln(sprintf("<fg=red>%s\t%s: %s</>", $_sDiffSign, $_sProperty, substr($_sDiffLine, 1)));
                } else {
                    $this->oOutput->writeln(sprintf("\t%s: %s", $_sProperty, $_sDiffLine));
                }
            }
        }

        $this->oOutput->writeln("\n");
    }
}
