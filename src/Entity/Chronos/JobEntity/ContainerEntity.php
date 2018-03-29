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
     * @param array|object $jobData
     * @throws \InvalidArgumentException
     */
    public function __construct($jobData = [])
    {
        if (is_array($jobData) || is_object($jobData)) {
            foreach ($jobData as $key => $value) {
                if (property_exists($this, $key)) {
                    if ($key == 'type') {
                        $this->{$key} = strtolower($value);
                    } elseif ($key == 'volumes') {
                        foreach ($value as $valueVolume) {
                            $volume = new ContainerVolumeEntity($valueVolume);
                            $this->volumes[] = $volume;
                        }
                    } else {
                        $this->{$key} = $value;
                    }
                } else {
                    $this->unknownFields[$key] = $value;
                }
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an array or object', __METHOD__));
        }
    }

    public $unknownFields = [];
    
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
