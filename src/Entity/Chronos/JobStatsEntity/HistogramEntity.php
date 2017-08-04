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
     * @param array $aHistogram
     */
    public function __construct(array $aHistogram = [])
    {
        foreach ($aHistogram as $_sKey => $_fValue) {
            switch ($_sKey) {
                case '75thPercentile':
                    $this->percentile75th = $_fValue;
                    break;

                case '95thPercentile':
                    $this->percentile95th = $_fValue;
                    break;

                case '98thPercentile':
                    $this->percentile98th = $_fValue;
                    break;

                case '99thPercentile':
                    $this->percentile99th = $_fValue;
                    break;

                default:
                    if (property_exists($this, $_sKey)) {
                        $this->{$_sKey} = $_fValue;
                    }
            }
        }
    }
}
