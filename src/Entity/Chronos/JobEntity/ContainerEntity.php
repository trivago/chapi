<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-04
 */

namespace Chapi\Entity\Chronos\JobEntity;

class ContainerEntity
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
                    if ($_sKey == 'type') {
                        $this->{$_sKey} = strtolower($_mValue);
                    } elseif ($_sKey == 'volumes') {
                        foreach ($_mValue as $_mValueVolume) {
                            $_oVolume = new ContainerVolumeEntity($_mValueVolume);
                            $this->volumes[] = $_oVolume;
                        }
                    } else {
                        $this->{$_sKey} = $_mValue;
                    }
                }
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an array or object', __METHOD__));
        }
    }
    
    /** @var string  */
    public $type = '';
    
    /** @var string  */
    public $image = '';
    
    /** @var string  */
    public $network = '';
    
    /** @var ContainerVolumeEntity[] */
    public $volumes = [];
    
    /** @var bool  */
    public $forcePullImage = false;
}
