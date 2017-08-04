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
     * @param array $stats
     */
    public function __construct(array $stats = [])
    {
        $this->histogram = (isset($stats['histogram'])) ? new HistogramEntity($stats['histogram']) : new HistogramEntity();
    }
}
