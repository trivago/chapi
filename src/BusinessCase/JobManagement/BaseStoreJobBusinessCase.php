<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 15/02/17
 * Time: 13:17
 */

namespace Chapi\BusinessCase\JobManagement;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

class BaseStoreJobBusinessCase
{
    /**
     * @var JobIndexServiceInterface
     */
    protected $oJobIndexService;


    /**
     * @var JobComparisonInterface
     */
    protected $oJobComparisionBusinessCase;

    /**
     * @var LoggerInterface
     */
    protected $oLogger;


    /**
     * @var JobRepositoryInterface
     */
    protected $oJobRepositoryRemote;

    /**
     * @var JobRepositoryInterface
     */
    protected $oJobRepositoryLocal;


    protected function addJobInLocalRepository(JobEntityInterface $oAppRemote)
    {
        if ($this->oJobRepositoryLocal->addJob($oAppRemote))
        {
            $this->oLogger->notice(sprintf(
                'App %s stored in local repository',
                $oAppRemote->getKey()
            ));
        }
        else {
            $this->oLogger->error(sprintf(
                'Failed to store %s in local repository',
                $oAppRemote->getKey()
            ));
        }
    }


    protected function updateJobInLocalRepository(JobEntityInterface $oAppRemote, $bForceOverwrite)
    {
        $_aDiff = $this->oJobComparisionBusinessCase->getJobDiff($oAppRemote->getKey());
        if (!empty($_aDiff))
        {
            if (!$bForceOverwrite)
            {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The app "%s" already exist in your local repository. Use the "force" option to overwrite the job',
                        $oAppRemote->getKey()
                    )
                );
            }

            if ($this->oJobRepositoryLocal->updateJob($oAppRemote))
            {
                $this->oLogger->notice(sprintf(
                    'App %s is updated in local repository',
                    $oAppRemote->getKey()
                ));
            }
            else {
                $this->oLogger->error(sprintf(
                    'Failed to update app %s in local repository',
                    $oAppRemote->getKey()
                ));
            }

            // remove job from index in case off added in the past
            $this->oJobIndexService->removeJob($oAppRemote->getKey());
        }
    }
}