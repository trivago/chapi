<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 18:16
 */

namespace Chapi\Entity\Marathon;

use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\AppEntity\Container;
use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;
use Chapi\Entity\Marathon\AppEntity\FetchUrl;
use Chapi\Entity\Marathon\AppEntity\HealthCheck;
use Chapi\Entity\Marathon\AppEntity\IpAddress;
use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\AppEntity\UpgradeStrategy;

class MarathonAppEntity implements JobEntityInterface
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

    public function __construct($oData)
    {
        // TODO: throw exception
        if ($oData == null) {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);

        if (property_exists($oData, "portDefinitions")) {
            foreach ($oData->portDefinitions as $portDefinition) {
                $this->portDefinitions[] = new DockerPortMapping($portDefinition);
            }
        }

        if (property_exists($oData, "container")) {
            $this->container = new Container($oData->container);
        }

        if (property_exists($oData, "fetch"))
        {
            foreach($oData->fetch as $fetch) {
                $this->fetch[] = new FetchUrl($fetch);
            }
        }

        if (property_exists($oData, "healthChecks"))
        {
            foreach($oData->healthChecks as $healthCheck)
            {
                $this->healthChecks[] = new HealthCheck($healthCheck);
            }
        }

        if (property_exists($oData, "upgradeStrategy"))
        {
            $this->upgradeStrategy = new UpgradeStrategy($oData->upgradeStrategy);
        }

        if (property_exists($oData, "ipAddress"))
        {
            $this->ipAddress = new IpAddress($oData->ipAddress);
        }

        MarathonEntityUtils::setPropertyIfExist($oData, $this, "env");
        MarathonEntityUtils::setPropertyIfExist($oData, $this, "constraints");
        MarathonEntityUtils::setPropertyIfExist($oData, $this, "acceptedResourceRoles");
        MarathonEntityUtils::setPropertyIfExist($oData, $this, "labels");
        MarathonEntityUtils::setPropertyIfExist($oData, $this, "dependencies");
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        return (array) $this;
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

    /**
     * @return string
     */
    public function getEntityType()
    {
        return JobEntityInterface::MARATHON_TYPE;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->id;
    }
}