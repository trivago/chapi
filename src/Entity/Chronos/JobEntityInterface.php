<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-10
 */


namespace Chapi\Entity\Chronos;


interface JobEntityInterface extends  \JsonSerializable, \IteratorAggregate
{
    /**
     * return entity as one-dimensional array
     *
     * @return mixed[]
     */
    public function getSimpleArrayCopy();

    /**
     * @return bool
     */
    public function isSchedulingJob();

    /**
     * @return bool
     */
    public function isDependencyJob();
}