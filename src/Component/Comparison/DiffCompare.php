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
    private static $compare;

    /**
     * @param mixed $valueA
     * @param mixed $valueB
     * @return string
     */
    public function compare($valueA, $valueB)
    {
        $compare = self::getCompare();

        $diff = $compare::compare(
            $this->valueToString($valueA),
            $this->valueToString($valueB)
        );

        return trim($compare::toString($diff));
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function valueToString($value)
    {
        if (is_array($value) || is_object($value)) {
            return trim(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return trim((string) $value);
    }

    /**
     * @return Diff
     */
    private static function getCompare()
    {
        if (is_null(self::$compare)) {
            self::$compare = new Diff();
        }

        return self::$compare;
    }
}
