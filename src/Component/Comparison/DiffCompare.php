<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-30
 *
 */
namespace Chapi\Component\Comparison;

use Chapi\Vendor\Diff;

class DiffCompare implements DiffCompareInterface
{
    /**
     * @var Diff
     */
    private static $oCompare;

    /**
     * @param mixed $mValueA
     * @param mixed $mValueB
     * @return string
     */
    public function compare($mValueA, $mValueB)
    {
        $_oCompare = self::getCompare();

        $_oDiff = $_oCompare::compare(
            $this->valueToString($mValueA),
            $this->valueToString($mValueB)
        );

        return trim($_oCompare::toString($_oDiff));
    }

    /**
     * @param mixed $mValue
     * @return string
     */
    private function valueToString($mValue)
    {
        if (is_array($mValue) || is_object($mValue)) {
            return trim(json_encode($mValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return trim((string) $mValue);
    }

    /**
     * @return Diff
     */
    private static function getCompare()
    {
        if (is_null(self::$oCompare)) {
            self::$oCompare = new Diff();
        }

        return self::$oCompare;
    }
}
