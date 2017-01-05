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
use Symfony\Component\Config\Definition\Exception\Exception;

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

    public $env = null;

    /**
     * @var array
     */
    public $constraints  = [];


    public $acceptedResourceRoles = null;

    public $labels = null;

    /**
     * @var FetchUrl[]
     */
    public $fetch = [];

    public $dependencies = [];

    /**
     * @var HealthCheck[]
     */
    public $healthChecks = [];

    public $backoffSeconds = 1;

    public $backoffFactor = 1.15;

    public $maxLaunchDelaySeconds = 3600;

    public $taskKillGracePeriodSeconds = 0;

    /**
     * @var UpgradeStrategy
     */
    public $upgradeStrategy = null;


    /**
     * @var IpAddress
     */
    public $ipAddress = null;

    public function __construct($mData = null)
    {
        if (!$mData)
        {
            // initialized with default values
            return;
        }

        // make sure data is array
        $aData = (array) $mData;

        MarathonEntityUtils::setAllPossibleProperties($aData, $this);

        if (isset($aData["portDefinitions"])) {
            foreach ($aData["portDefinitions"] as $portDefinition) {
                $this->portDefinitions[] = new DockerPortMapping((array)$portDefinition);
            }
        }

        if (isset($aData["container"])) {
            $this->container = new Container((array)$aData["container"]);
        }

        if (isset($aData["fetch"]))
        {
            foreach($aData["fetch"] as $fetch) {
                $this->fetch[] = new FetchUrl((array)$fetch);
            }
        }

        if (isset($aData["healthChecks"]))
        {
            foreach($aData["healthChecks"] as $healthCheck)
            {
                $this->healthChecks[] = new HealthCheck((array)$healthCheck);
            }
        }

        if (isset($aData["upgradeStrategy"]))
        {
            $this->upgradeStrategy = new UpgradeStrategy((array)$aData["upgradeStrategy"]);
        } else {
            $this->upgradeStrategy = new UpgradeStrategy();
        }

        if (isset($aData["ipAddress"]))
        {
            $this->ipAddress = new IpAddress((array)$aData["ipAddress"]);
        }

        if (isset($aData["env"]))
        {
            $this->env =  (object) $aData["env"];
        }

        if (isset($aData["labels"]))
        {
            $this->labels = (object) $aData["labels"];
        }
        MarathonEntityUtils::setPropertyIfExist($aData, $this, "constraints");
        MarathonEntityUtils::setPropertyIfExist($aData, $this, "acceptedResourceRoles");
        MarathonEntityUtils::setPropertyIfExist($aData, $this, "dependencies");
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        $_aRet = (array) $this;
        return $_aRet;
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
        $_aReturn = [];

        foreach ($this as $_sProperty => $mValue)
        {
            $_aReturn[$_sProperty] = (is_array($mValue) || is_object($mValue)) ? json_encode($mValue) : $mValue;
        }

        return $_aReturn;
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
        return count($this->dependencies) ? true : false;
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