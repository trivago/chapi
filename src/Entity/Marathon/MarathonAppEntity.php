<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */

namespace Chapi\Entity\Marathon;

use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\AppEntity\Container;
use Chapi\Entity\Marathon\AppEntity\HealthCheck;
use Chapi\Entity\Marathon\AppEntity\IpAddress;
use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\AppEntity\UpgradeStrategy;

class MarathonAppEntity implements JobEntityInterface
{
    public $id = '';

    public $cmd = null;

    public $cpus = 0;

    public $mem = 0;

    public $args = null;

    /**
     * @var PortDefinition[]
     */
    public $portDefinitions = null;

    public $requirePorts = false;

    public $instances = 0;

    public $executor = '';

    /**
     * @var Container
     */
    public $container = null;

    public $env = null;

    /**
     * @var array
     */
    public $constraints = [];


    public $acceptedResourceRoles = null;

    public $labels = null;

    public $uris = [];

    public $dependencies = [];

    /**
     * @var HealthCheck[]
     */
    public $healthChecks = null;

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

    public function __construct($data = null)
    {
        if (!$data) {
            // initialized with default values
            return;
        }

        // make sure data is array
        $dataArray = (array) $data;

        MarathonEntityUtils::setAllPossibleProperties($dataArray, $this);

        if (isset($dataArray['portDefinitions'])) {
            foreach ($dataArray['portDefinitions'] as $portDefinition) {
                $this->portDefinitions[] = new PortDefinition((array) $portDefinition);
            }
        }

        if (isset($dataArray['container'])) {
            $this->container = new Container((array) $dataArray['container']);
        }

        if (isset($dataArray['healthChecks'])) {
            foreach ($dataArray['healthChecks'] as $healthCheck) {
                $this->healthChecks[] = new HealthCheck((array) $healthCheck);
            }
        }

        if (isset($dataArray['upgradeStrategy'])) {
            $this->upgradeStrategy = new UpgradeStrategy((array) $dataArray['upgradeStrategy']);
        } else {
            $this->upgradeStrategy = new UpgradeStrategy();
        }

        if (isset($dataArray['ipAddress'])) {
            $this->ipAddress = new IpAddress((array) $dataArray['ipAddress']);
        }

        if (isset($dataArray['env'])) {
            $env = (array) $dataArray['env'];

            // sorting this makes the diff output a whole lot more readable
            ksort($env);

            $this->env = (object) $env;
        } else {
            $this->env = (object) [];
        }

        if (isset($dataArray['labels'])) {
            $this->labels = (object) $dataArray['labels'];
        } else {
            $this->labels = (object) [];
        }
        MarathonEntityUtils::setPropertyIfExist($dataArray, $this, 'constraints');
        MarathonEntityUtils::setPropertyIfExist($dataArray, $this, 'args');
        MarathonEntityUtils::setPropertyIfExist($dataArray, $this, 'uris');
        MarathonEntityUtils::setPropertyIfExist($dataArray, $this, 'acceptedResourceRoles');
        MarathonEntityUtils::setPropertyIfExist($dataArray, $this, 'dependencies');
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        $return = (array) $this;
        $return = array_filter(
            $return,
            function ($value, $key) {
                return !is_null($value) || empty($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
        return $return;
    }

    /**
     * @inheritdoc
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getSimpleArrayCopy()
    {
        $_aReturn = [];

        foreach ($this as $_sProperty => $mValue) {
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
