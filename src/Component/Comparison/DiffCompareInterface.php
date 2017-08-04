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
     * @param mixed $mValueA
     * @param mixed $mValueB
     * @return string
     */
    public function compare($mValueA, $mValueB);
}
