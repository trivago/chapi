<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-30
 *
 */
namespace Chapi\Component\Comparison;

interface DiffCompareInterface
{
    const DIC_NAME = 'DiffCompareInterface';

    /**
     * @param mixed $valueA
     * @param mixed $valueB
     * @return string
     */
    public function compare($valueA, $valueB);
}
