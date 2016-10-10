<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 18:16
 */

namespace Chapi\Entity\Marathon;

class JobMainEntity
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

}