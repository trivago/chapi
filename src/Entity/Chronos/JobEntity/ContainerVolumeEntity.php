<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-04
 */

namespace Chapi\Entity\Chronos\JobEntity;

class ContainerVolumeEntity
{
    /**
     * @param array|object $mJobData
     * @throws \InvalidArgumentException
     */
    public function __construct($mJobData = [])
    {
        if (is_array($mJobData) || is_object($mJobData)) {
            foreach ($mJobData as $_sKey => $_mValue) {
                if (property_exists($this, $_sKey)) {
                    $this->{$_sKey} = $_mValue;
                }
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an array or object', __METHOD__));
        }
    }
    
    /** @var string  */
    public $containerPath = '';

    /** @var string  */
    public $hostPath = '';

    /**
     * @var string
     *
     * read-write and read-only.
     * val RW, RO = Value
     */
    public $mode = '';
}
