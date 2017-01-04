<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 03/01/17
 * Time: 14:41
 */

namespace Chapi\BusinessCase\JobManagement;


use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

class MarathonStoreJobBusinessCase implements StoreJobBusinessCaseInterface
{

    /**
     * @var JobIndexServiceInterface
     */
    private $oJobIndexService;
    /**
     * @var LoggerInterface
     */
    private $oLogger;
    /**
     * @var JobComparisonInterface
     */
    private $oJobComparisionBusinessCase;
    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryRemote;
    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryLocal;

    public function __construct(
        JobIndexServiceInterface $oJobIndexService,
        JobRepositoryInterface $oJobRepositoryRemote,
        JobRepositoryInterface $oJobRepositoryLocal,
        JobComparisonInterface $oJobComparisionBusinessCase,
        LoggerInterface $oLogger

    )
    {

        $this->oJobIndexService = $oJobIndexService;
        $this->oLogger = $oLogger;
        $this->oJobComparisionBusinessCase = $oJobComparisionBusinessCase;
        $this->oJobRepositoryRemote = $oJobRepositoryRemote;
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
    }

    /**
     * @return void
     */
    public function storeIndexedJobs()
    {
        $_aRemoteMissingApps = $this->oJobComparisionBusinessCase->getRemoteMissingJobs();
        foreach ($_aRemoteMissingApps as $_sAppId)
        {
            $this->addRemoteMissingApp($_sAppId);
        }

        $_aLocalMissingApps = $this->oJobComparisionBusinessCase->getLocalMissingJobs();
        foreach ($_aLocalMissingApps as $_sAppId)
        {
            $this->removeLocalMissingAppInRemote($_sAppId);
        }
        $_aLocalUpdates = $this->oJobComparisionBusinessCase->getLocalJobUpdates();
        foreach ($_aLocalUpdates as $_sAppId)
        {
            $this->updateAppInRemote($_sAppId);
        }
    }

    private function addRemoteMissingApp($sAppId)
    {
        if ($this->oJobIndexService->isJobInIndex($sAppId))
        {
            /** @var JobEntityInterface $_oJobEntityLocal */
            $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($sAppId);
            if ($this->oJobRepositoryRemote->addJob($_oJobEntityLocal))
            {
                $this->oJobIndexService->removeJob($_oJobEntityLocal->getKey());
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully added to marathon',
                    $_oJobEntityLocal->getKey()
                ));

                return true;
            }
            $this->oLogger->error(sprintf(
                'Job "%s" successfully added to marathon',
                $_oJobEntityLocal->getKey()
            ));
        }
        return false;

    }


    private function removeLocalMissingAppInRemote($sAppId)
    {
        if ($this->oJobIndexService->isJobInIndex($sAppId))
        {
            if ($this->oJobRepositoryRemote->removeJob($sAppId))
            {
                $this->oJobIndexService->removeJob($sAppId);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully removed from marathon',
                    $sAppId
                ));

                return true;
            }
            $this->oLogger->error(sprintf(
                'Failed to remove"%s" from marathon',
                $sAppId
            ));

        }
        return false;
    }

    private function updateAppInRemote($sAppId)
    {
        if ($this->oJobIndexService->isJobInIndex($sAppId))
        {
            $_bRemoved = $this->oJobRepositoryRemote->removeJob($sAppId);
            $_bAddedBack = $this->oJobRepositoryRemote->addJob($sAppId);

            // updated
            if ($_bRemoved && $_bAddedBack)
            {
                $this->oJobIndexService->removeJob($sAppId);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully updated in marathon',
                    $sAppId
                ));

                return true;
            }

            $this->oLogger->error(sprintf(
                'Failed to update job "%s" in marathon',
                $sAppId
            ));
        }

        return false;

    }

    /**
     * @param array $aJobNames
     * @param bool|false $bForceOverwrite
     * @return void
     */
    public function storeJobsToLocalRepository(array $aJobNames = [], $bForceOverwrite = false)
    {
        if (empty($aJobNames))
        {
            $_aApps = $this->oJobRepositoryRemote->getJobs();
        }
        else
        {
            $_aApps = [];
            foreach ($aJobNames as $_sAppName)
            {
                $_aApps[] = $this->oJobRepositoryRemote->getJob($_sAppName);
            }
        }

        /** @var JobEntityInterface $_oApp */
        foreach ($_aApps as $_oAppRemote)
        {
            $_oAppLocal = $this->oJobRepositoryLocal->getJob($_oAppRemote->getKey());
            if (null == $_oAppLocal) // add
            {
                if ($this->oJobRepositoryLocal->addJob($_oAppRemote))
                {
                    $this->oLogger->notice(sprintf(
                           'App %s stored in local repository',
                        $_oAppRemote->getKey()
                        ));
                }
                else {
                    $this->oLogger->error(sprintf(
                        'Failed to store %s in local repository',
                        $_oAppRemote->getKey()
                    ));
                }
            }
            else // update
            {
                $_aDiff = $this->oJobComparisionBusinessCase->getJobDiff($_oAppRemote->getKey());
                if (!empty($_aDiff))
                {
                    if (!$bForceOverwrite)
                    {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The app "%s" already exist in your local repository. Use the "force" option to overwrite the job',
                                $_oAppRemote->getKey()
                            )
                        );
                    }

                    if ($this->oJobRepositoryLocal->updateJob($_oAppRemote))
                    {
                        $this->oLogger->notice(sprintf(
                           'App %s is updated in local repository',
                            $_oAppRemote->getKey()
                        ));
                    }
                    else{
                        $this->oLogger->error(sprintf(
                           'Failed to update app %s in local repository',
                            $_oAppRemote->getKey()
                        ));
                    }

                    // remove job from index in case off added in the past
                    $this->oJobIndexService->removeJob($_oAppRemote->getKey());
                }
            }
        }
    }

    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobAvailable($sJobName)
    {
        $_bLocallyAvailable = $this->oJobRepositoryLocal->getJob($sJobName) ? true : false;
        $_bRemotelyAvailable = $this->oJobRepositoryRemote->getJob($sJobName) ? true : false;
        return $_bLocallyAvailable || $_bRemotelyAvailable;
    }
}