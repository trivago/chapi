<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */

namespace Chapi\Entity\Chronos;

class JobEntity
{
    public $name = '';

    public $command = '';

    public $description = '';

    public $owner = '';

    public $ownerName = '';

    public $schedule = ''; // todo: move to separate entity

    public $scheduleTimeZone = 'Europe/Berlin'; // todo: add time zone to config

    public $epsilon = 'PT15M';

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

    public $runAsUser =  'root';


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
                    $this->{$_sKey} = $_mValue;
                }
            }
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to "%s" must be an array or object', __METHOD__));
        }
    }
}