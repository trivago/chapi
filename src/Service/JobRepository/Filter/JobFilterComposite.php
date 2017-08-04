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
    private $filters = [];

    /**
     * JobFilterComposite constructor.
     * @param JobFilterInterface[] $filters
     */
    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $jobEntity)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->isInteresting($jobEntity)) {
                return false;
            }
        }

        return true;
    }
}
