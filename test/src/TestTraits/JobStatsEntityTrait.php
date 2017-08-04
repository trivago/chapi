<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-11
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */


namespace ChapiTest\src\TestTraits;

use Chapi\Entity\Chronos\JobStatsEntity;

trait JobStatsEntityTrait
{
    /**
     * @return JobStatsEntity
     */
    private function createValidJobStatsEntity()
    {
        $_aTestValues = [
            'histogram' => [
                '75thPercentile' => 60000.0,
                '95thPercentile' => 70000.0,
                '98thPercentile' => 80000.0,
                '99thPercentile' => 90000.0,
                'median' => 100000.10,
                'mean' => 110000.22,
                'count' => 10
            ],
            'taskStatHistory' => []
        ];

        return new JobStatsEntity($_aTestValues);
    }
}
