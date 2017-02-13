<?php
/**
 *
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-12-14
 *
 */

namespace Chapi\BusinessCase\Comparison;


use Chapi\Component\Comparison\DiffCompareInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\AppEntity\FetchUrl;
use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;

class MarathonJobComparisonBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryInterface
     */
    private $oRemoteRepository;
    /**
     * @var JobRepositoryInterface
     */
    private $oLocalRepository;
    /**
     * @var DiffCompareInterface
     */
    private $oDiffCompare;

    /**
     * @param JobRepositoryInterface $oLocalRepository
     * @param JobRepositoryInterface $oRemoteRepository
     * @param DiffCompareInterface $oDiffCompare
     */
    public function __construct(
        JobRepositoryInterface $oLocalRepository,
        JobRepositoryInterface $oRemoteRepository,
        DiffCompareInterface $oDiffCompare
    )
    {

        $this->oRemoteRepository = $oRemoteRepository;
        $this->oLocalRepository = $oLocalRepository;
        $this->oDiffCompare = $oDiffCompare;
    }
    /**
     * @return array<string>
     */
    public function getLocalMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oLocalRepository->getJobs(),
            $this->oRemoteRepository->getJobs()
        );
    }

    /**
     * @return array<string>
     */
    public function getRemoteMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oRemoteRepository->getJobs(),
            $this->oLocalRepository->getJobs()
        );
    }

    /**
     * @return array<string>
     */
    public function getLocalJobUpdates()
    {
        $_aLocallyUpdatedJobs = [];
        $_aLocalJobs = $this->oLocalRepository->getJobs();

        /** @var JobEntityInterface $_oLocalJob */
        foreach($_aLocalJobs as $_oLocalJob)
        {
            $_oRemoteJob = $this->oRemoteRepository->getJob($_oLocalJob->getKey());
            if (!$_oRemoteJob)
            {
                // if doesn't exist in remote, its not update. its new
                continue;
            }

            // marathon returns portDefinitions values for auto configured port as well
            // we want to only check if the port is defined in local file.
            // otherwise we ignore the remote values.
            if (empty($_oLocalJob->portDefinitions)) {
                $_oRemoteJob->portDefinitions = [];
            }

            $_aNonIdenticalProps = $this->compareJobEntities($_oLocalJob, $_oRemoteJob);

            if (!empty($_aNonIdenticalProps))
            {
                $_aLocallyUpdatedJobs[] = $_oLocalJob->getKey();
            }
        }

        return $_aLocallyUpdatedJobs;
    }

    /**
     * @param string $sJobName
     * @return array
     */
    public function getJobDiff($sJobName)
    {
        $_aDifferences = [];
        $_oLocalJob = $this->oLocalRepository->getJob($sJobName);
        $_oRemoteJob = $this->oRemoteRepository->getJob($sJobName);

        if (!$_oLocalJob && !$_oRemoteJob)
        {
            // return as jobs doesnt exist
            return [];
        }

        if (!$_oLocalJob)
        {
            $_oLocalJob = new MarathonAppEntity(null);
        }

        if (!$_oRemoteJob)
        {
            $_oRemoteJob = new MarathonAppEntity(null);
        }

        $_aNonIdenticalProps = $this->compareJobEntities(
            $_oLocalJob,
            $_oRemoteJob
        );

        foreach ($_aNonIdenticalProps as $_sProperty)
        {
            $_aDifferences[$_sProperty] = $this->oDiffCompare->compare(
                $_oRemoteJob->{$_sProperty},
                $_oLocalJob->{$_sProperty}
            ) ;
        }

        return $_aDifferences;
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityA
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        // for now we don't have a concrete seperation
        // of types for marathon.
        return true;
    }

    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobAvailable($sJobName)
    {
        $_bLocallyAvailable = $this->oLocalRepository->getJob($sJobName);
        $_bRemotelyAvailable = $this->oRemoteRepository->getJob($sJobName);
        return $_bLocallyAvailable || $_bRemotelyAvailable;
    }


    /**
     * @param JobCollection $oJobCollectionA
     * @param JobCollection $oJobCollectionB
     * @return string[]
     */
    private function getMissingJobsInCollectionA(JobCollection $oJobCollectionA, JobCollection $oJobCollectionB)
    {
        return array_diff(
            array_keys($oJobCollectionB->getArrayCopy()),
            array_keys($oJobCollectionA->getArrayCopy())
        );
    }

    /**
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return array
     */
    private function compareJobEntities(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        $_aNonidenticalProperties = [];

        $aJobACopy = [];
        $aJobBCopy = [];

        if ($oJobEntityA)
        {
            $aJobACopy = $oJobEntityA->getSimpleArrayCopy();
        }

        if ($oJobEntityB)
        {
            $aJobBCopy = $oJobEntityB->getSimpleArrayCopy();
        }

        $_aDiff = array_merge(
            array_diff_assoc(
                $aJobACopy,
                $aJobBCopy
            ),
            array_diff_assoc(
                $aJobBCopy,
                $aJobACopy
            )
        );

        if (count($_aDiff) > 0)
        {
            $_aDiffKeys = array_keys($_aDiff);

            foreach ($_aDiffKeys as $_sDiffKey)
            {
                if (!$this->isEntityEqual($_sDiffKey, $oJobEntityA, $oJobEntityB))
                {
                    $_aNonidenticalProperties[] = $_sDiffKey;
                }
            }
        }

        return $_aNonidenticalProperties;
    }

    /**
     * @param $sProperty
     * @param $oJobEntityA
     * @param $oJobEntityB
     * @return bool
     */
    private function isEntityEqual($sProperty, $oJobEntityA, $oJobEntityB)
    {
        return $this->isEqual($oJobEntityA->{$sProperty}, $oJobEntityB->{$sProperty});
    }

    /**
     * @param mixed $mValueA
     * @param mixed $mValueB
     * @return bool
     */
    private function isEqual($mValueA, $mValueB)
    {
        if (is_array($mValueA) && is_array($mValueB)) {
            return $this->isArrayEqual($mValueA, $mValueB);
        }
        elseif (is_object($mValueA) && is_object($mValueB))
        {
            return $this->isArrayEqual(get_object_vars($mValueA), get_object_vars($mValueB));
        }
        elseif ((is_scalar($mValueA) && is_scalar($mValueB)) || (is_null($mValueA) && is_null($mValueB)))
        {
            return $mValueA == $mValueB;
        }

        return false;
    }

    /**
     * @param array $aValuesA
     * @param array $aValuesB
     * @return bool
     */
    private function isArrayEqual(array $aValuesA, array $aValuesB)
    {
        return $this->isArrayHalfEqual($aValuesA, $aValuesB) && $this->isArrayHalfEqual($aValuesB, $aValuesA);
    }

    /**
     * @param array $aValuesA
     * @param array $aValuesB
     * @return bool
     */
    private function isArrayHalfEqual(array $aValuesA, array $aValuesB)
    {
        foreach ($aValuesA as $_mKeyA => $_mValueA)
        {
            if (is_string($_mKeyA))
            {
                if (!array_key_exists($_mKeyA, $aValuesB) || !$this->isEqual($_mValueA, $aValuesB[$_mKeyA]))
                {
                    return false;
                }
            }
            else
            {
                foreach ($aValuesB as $_mValueB)
                {
                    if ($_mValueA == $_mValueB)
                    {
                        continue 2;
                    }
                }

                return false;
            }
        }

        return true;
    }
}
