<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-09
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace Chapi\Entity\Chronos\JobStatsEntity;

class HistogramEntity
{
    /** @var float  */
    public $percentile75th = 0.0;

    /** @var float  */
    public $percentile95th = 0.0;

    /** @var float  */
    public $percentile98th = 0.0;

    /** @var float  */
    public $percentile99th = 0.0;

    /** @var float  */
    public $median = 0.0;

    /** @var float  */
    public $mean = 0.0;

    /** @var int  */
    public $count = 0;

    /**
     * @param array $histogram
     */
    public function __construct(array $histogram = [])
    {
        foreach ($histogram as $key => $value) {
            switch ($key) {
                case '75thPercentile':
                    $this->percentile75th = $value;
                    break;

                case '95thPercentile':
                    $this->percentile95th = $value;
                    break;

                case '98thPercentile':
                    $this->percentile98th = $value;
                    break;

                case '99thPercentile':
                    $this->percentile99th = $value;
                    break;

                default:
                    if (property_exists($this, $key)) {
                        $this->{$key} = $value;
                    }
            }
        }
    }
}
