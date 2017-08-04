<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-10
 */


namespace Chapi\Entity;

interface JobEntityInterface extends \JsonSerializable, \IteratorAggregate
{
    const MARATHON_TYPE = 'marathon';
    const CHRONOS_TYPE = 'chronos';
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

    /**
     * @return string
     */
    public function getEntityType();

    /**
     * @return string
     */
    public function getKey();
}
