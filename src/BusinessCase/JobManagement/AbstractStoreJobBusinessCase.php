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

abstract class AbstractStoreJobBusinessCase implements StoreJobBusinessCaseInterface
{
    /**
     * @var JobIndexServiceInterface
     */
    protected $oJobIndexService;


    /**
     * @var JobComparisonInterface
     */
    protected $oJobComparisonBusinessCase;

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


    /**
     * @inheritdoc
     */
    public function storeJobsToLocalRepository(array $aEntityNames = [], $bForceOverwrite = false)
    {
        if (empty($aEntityNames))
        {
            $_aRemoteEntities = $this->oJobRepositoryRemote->getJobs();
        }
        else
        {
            $_aRemoteEntities = [];
            foreach ($aEntityNames as $_sJobName)
            {
                $_aRemoteEntities[] = $this->oJobRepositoryRemote->getJob($_sJobName);
            }
        }

        /** @var JobEntityInterface $_oRemoteEntity */
        foreach ($_aRemoteEntities as $_oRemoteEntity)
        {
            $_oLocalEntity = $this->oJobRepositoryLocal->getJob($_oRemoteEntity->getKey());
            // new job
            if (null == $_oLocalEntity)
            {
                $this->addJobInLocalRepository($_oRemoteEntity);
            }
            else {
                //update
                $this->updateJobInLocalRepository($_oRemoteEntity, $bForceOverwrite);
            }
        }
    }

    protected function addJobInLocalRepository(JobEntityInterface $oAppRemote)
    {
        if ($this->oJobRepositoryLocal->addJob($oAppRemote))
        {
            $this->oLogger->notice(sprintf(
                'Entity %s stored in local repository',
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
        $_aDiff = $this->oJobComparisonBusinessCase->getJobDiff($oAppRemote->getKey());
        if (!empty($_aDiff))
        {
            if (!$bForceOverwrite)
            {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The entity "%s" already exist in your local repository. Use the "force" option to overwrite the job',
                        $oAppRemote->getKey()
                    )
                );
            }

            if ($this->oJobRepositoryLocal->updateJob($oAppRemote))
            {
                $this->oLogger->notice(sprintf(
                    'Entity %s is updated in local repository',
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

    /**
     * @inheritdoc
     */
    public function isJobAvailable($sJobName)
    {
        $_bLocallyAvailable = $this->oJobRepositoryLocal->getJob($sJobName) ? true : false;
        $_bRemotelyAvailable = $this->oJobRepositoryRemote->getJob($sJobName) ? true : false;
        return $_bLocallyAvailable || $_bRemotelyAvailable;
    }


    /**
     * @inheritdoc
     */
    abstract public function storeIndexedJobs();
}