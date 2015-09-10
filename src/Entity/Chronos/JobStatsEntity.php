<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-09
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace Chapi\Entity\Chronos;

use Chapi\Entity\Chronos\JobStatsEntity\HistogramEntity;

class JobStatsEntity
{
    /**
     * @var HistogramEntity
     */
    public $histogram;

    /** @todo test and implement "taskStatHistory" */
    public $taskStatHistory;

    /**
     * @param array $aStats
     */
    public function __construct(array $aStats = [])
    {
        $this->histogram = (isset($aStats['histogram'])) ? new HistogramEntity($aStats['histogram']) : new HistogramEntity();
    }
}