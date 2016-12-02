<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 18:16
 */

namespace Chapi\Entity\Marathon;

use Chapi\Entity\JobEntityInterface;

class AppMainEntity implements JobEntityInterface
{
    public $id = "";

    public $cmd = "";

    public $cpus = 0;

    public $mem = 0;

    /**
     * @var PortDefinition[]
     */
    public $portDefinitions =[];

    public $requirePorts = false;

    public $instances = 0;

    public $executor = "";

    /**
     * @var Container
     */
    public $container = null;

    public $env = [];

    /**
     * @var array
     */
    public $constraints  = [];


    public $acceptedResourceRoles = [];

    public $labels = [];

    /**
     * @var FetchUrl[]
     */
    public $fetch = [];

    public $dependencies = [];

    /**
     * @var HealthCheck[]
     */
    public $healthChecks = [];

    public $backoffSeconds = 0;

    public $backoffFactor = 1;

    public $maxLaunchDelaySeconds = 0;

    public $taskKillGracePeriodSeconds = 0;

    /**
     * @var UpgradeStrategy
     */
    public $upgradeStrategy = null;


    /**
     * @var IpAddress
     */
    public $ipAddress = null;

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {

        //TODO: test data for now
        return ["hello" => "world"];
    }

    /**
     * @inheritdoc
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        // what does this do? Return an array? But how do nested objects come into play?
        return new \ArrayIterator($this);
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getSimpleArrayCopy()
    {
        return [];
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function isSchedulingJob()
    {
        return false;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function isDependencyJob()
    {
        return false;
    }

}