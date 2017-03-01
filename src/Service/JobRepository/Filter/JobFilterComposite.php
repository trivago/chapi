<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-01
 *
 */

namespace Chapi\Service\JobRepository\Filter;


use Chapi\Entity\JobEntityInterface;

class JobFilterComposite implements JobFilterInterface
{
    /**
     * @var JobFilterInterface[]
     */
    private $aFilter = [];

    /**
     * JobFilterComposite constructor.
     * @param JobFilterInterface[] $aFilter
     */
    public function __construct($aFilter)
    {
        $this->aFilter = $aFilter;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $oJobEntity)
    {
        foreach ($this->aFilter as $oFilter)
        {
            if (!$oFilter->isInteresting($oJobEntity))
            {
                return false;
            }
        }

        return true;
    }
}