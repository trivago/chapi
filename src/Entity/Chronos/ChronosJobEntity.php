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
use Chapi\Entity\Chronos\JobEntity\FetchEntity;
use Chapi\Entity\JobEntityInterface;

class ChronosJobEntity implements JobEntityInterface
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

    /** @var FetchEntity[] */
    public $fetch = [];

    public $environmentVariables = [];

    public $arguments = [];

    public $highPriority = false;

    public $runAsUser = 'root';

    public $constraints = [];

    /** @var ContainerEntity */
    public $container = null;


    /**
     * @param array|object $jobData
     * @throws \InvalidArgumentException
     */
    public function __construct($jobData = [])
    {
        if (is_array($jobData) || is_object($jobData)) {
            foreach ($jobData as $key => $value) {
                if (property_exists($this, $key)) {
                    if ($key == 'container') {
                        $this->{$key} = new ContainerEntity($value);
                    } else if ($key == 'fetch') {
                        foreach ($value as $fetch) {
                            $this->{$key}[] = new FetchEntity($fetch);
                        }
                    } else {
                        $this->{$key} = $value;
                    }
                } else {
                    /* We are ignoring fields that are unknown to us. This is bad and can lead to unexpected differences
                     * when comparing the *.json on disk with the job definition from the Chronos API.
                     */
                }
            }
        } else {
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
        $return = [];

        foreach ($this as $property => $value) {
            $return[$property] = (is_array($value) || is_object($value)) ? json_encode($value) : $value;
        }

        return $return;
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
        $return = (array) $this;
        if (!empty($this->schedule)) {
            unset($return['parents']);
        } else {
            unset($return['schedule']);
            unset($return['scheduleTimeZone']);
        }

        if (empty($this->container)) {
            unset($return['container']);
        } else {
            $return['container'] = (array) $this->container;

            $return['container']['volumes'] = [];
            foreach ($this->container->volumes as $volume) {
                $return['container']['volumes'][] = (array) $volume;
            }
        }

        $return['fetch'] = [];
        foreach ($this->fetch as $fetch) {
            $return['fetch'][] = (array) $fetch;
        }

        unset($return['successCount']);
        unset($return['errorCount']);
        unset($return['errorsSinceLastSuccess']);
        unset($return['lastSuccess']);
        unset($return['lastError']);

        return $return;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return JobEntityInterface::CHRONOS_TYPE;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->name;
    }
}
