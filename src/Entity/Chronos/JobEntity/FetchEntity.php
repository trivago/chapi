<?php
/**
 * @package: chapi
 *
 * @author:  sbrueggen
 * @since:   2018-03-29
 */

namespace Chapi\Entity\Chronos\JobEntity;

class FetchEntity
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
                    $this->{$key} = $value;
                }
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an array or object', __METHOD__));
        }
    }
    
    /** @var string  */
    public $uri = "";
    
    /** @var string  */
    public $destPath = '';
    
    /** @var bool */
    public $extract = false;
    
    /** @var bool  */
    public $cache = false;
    
    /** @var bool  */
    public $executable = false;
}
