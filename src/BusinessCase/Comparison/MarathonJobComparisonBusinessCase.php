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
use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;

class MarathonJobComparisonBusinessCase extends AbstractJobComparisionBusinessCase
{
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

    protected function preCompareModifications(JobEntityInterface &$oLocalJob, JobEntityInterface &$oRemoteJob)
    {
        if (
            !$oLocalJob instanceof MarathonAppEntity ||
            !$oRemoteJob instanceof MarathonAppEntity
        )
        {
            throw new \RuntimeException('Required MarathonAppEntity. Something else encountered.');
        }
        // marathon returns portDefinitions values for auto configured port as well
        // we want to only check if the port is defined in local file.
        // otherwise we ignore the remote values.
        if (!$oLocalJob->portDefinitions)
        {
            $oRemoteJob->portDefinitions = null;
        }
    }

    /**
     * @return JobEntityInterface
     */
    protected function getEntitySetWithDefaults()
    {
        return new MarathonAppEntity();
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
     * @param $sProperty
     * @param $oJobEntityA
     * @param $oJobEntityB
     * @return bool
     */
    protected function isEntityEqual($sProperty, JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        if (
            !$oJobEntityA instanceof MarathonAppEntity ||
            !$oJobEntityB instanceof MarathonAppEntity
        )
        {
            throw new \RuntimeException('Required MarathonAppEntity. Something else encountered.');
        }

        return $this->isEqual($oJobEntityA->{$sProperty}, $oJobEntityB->{$sProperty});
    }

    /**
     * @param mixed $mValueA
     * @param mixed $mValueB
     * @return bool
     */
    private function isEqual($mValueA, $mValueB)
    {
        if (is_array($mValueA) && is_array($mValueB))
        {
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
