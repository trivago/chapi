<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */

namespace Chapi\Entity\Chronos;

use Chapi\Entity\Chronos\JobEntity\ContainerEntity;

class JobEntity implements JobEntityInterface
{
    public $name = '';

    public $command = '';

    public $description = '';

    public $owner = '';

    public $ownerName = '';

    public $schedule = ''; // todo: move to separate entity

    public $scheduleTimeZone = '';

    public $parents = []; // todo: move to separate entity

    public $epsilon = '';

    public $executor = '';

    public $executorFlags = '';

    public $shell = true;

    public $retries = 0;

    public $async = false;

    public $successCount = 0;

    public $errorCount = 0;

    public $errorsSinceLastSuccess = 0;

    public $lastSuccess = '';

    public $lastError = '';

    public $cpus = 0.1;

    public $disk = 24;

    public $mem = 32;

    public $disabled = false;

    public $softError = false;

    public $dataProcessingJobType = false;

    public $uris = [];

    public $environmentVariables = [];

    public $arguments = [];

    public $highPriority = false;

    public $runAsUser = 'root';

    public $constraints = [];

    /** @var ContainerEntity */
    public $container = null;


    /**
     * @param array|object $mJobData
     * @throws \InvalidArgumentException
     */
    public function __construct($mJobData = [])
    {
        if (is_array($mJobData) || is_object($mJobData))
        {
            foreach ($mJobData as $_sKey => $_mValue)
            {
                if (property_exists($this, $_sKey))
                {
                    if ($_sKey == 'container')
                    {
                        $this->{$_sKey} = new ContainerEntity($_mValue);
                    }
                    else
                    {
                        $this->{$_sKey} = $_mValue;    
                    }
                }
            }
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an array or object', __METHOD__));
        }
    }

    /**
     * return entity as one-dimensional array
     *
     * @return mixed[]
     */
    public function getSimpleArrayCopy()
    {
        $_aReturn = [];

        foreach ($this as $_sProperty => $mValue)
        {
            $_aReturn[$_sProperty] = (is_array($mValue) || is_object($mValue)) ? json_encode($mValue) : $mValue;
        }

        return $_aReturn;
    }

    /**
     * @return bool
     */
    public function isSchedulingJob()
    {
        return (!empty($this->schedule) && empty($this->parents));
    }

    /**
     * @return bool
     */
    public function isDependencyJob()
    {
        return (empty($this->schedule) && !empty($this->parents));
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $_aReturn = (array) $this;
        if (!empty($this->schedule))
        {
            unset($_aReturn['parents']);
        }
        else
        {
            unset($_aReturn['schedule']);
            unset($_aReturn['scheduleTimeZone']);
        }

        if (empty($this->container))
        {
            unset($_aReturn['container']);
        }

        unset($_aReturn['successCount']);
        unset($_aReturn['errorCount']);
        unset($_aReturn['errorsSinceLastSuccess']);
        unset($_aReturn['lastSuccess']);
        unset($_aReturn['lastError']);

        return $_aReturn;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
