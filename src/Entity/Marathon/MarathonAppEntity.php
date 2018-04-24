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
use Chapi\Entity\Marathon\AppEntity\Fetch;
use Chapi\Entity\Marathon\AppEntity\HealthCheck;
use Chapi\Entity\Marathon\AppEntity\IpAddress;
use Chapi\Entity\Marathon\AppEntity\Network;
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

    /**
     * @var Network[]
     */
    public $networks = [];

    public $env = null;

    /**
     * @var array
     */
    public $constraints = [];

    public $acceptedResourceRoles = null;

    public $labels = null;

    public $uris = [];

    /**
     * @var Fetch[]
     */
    public $fetch = [];

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

        MarathonEntityUtils::setAllPossibleProperties(
            $dataArray,
            $this,
            array(
                'portDefinitions' => MarathonEntityUtils::convertToArrayOfClass(PortDefinition::class),
                'container' => MarathonEntityUtils::convertToClass(Container::class),
                'networks' => MarathonEntityUtils::convertToArrayOfClass(Network::class),
                'fetch' => MarathonEntityUtils::convertToArrayOfClass(Fetch::class),
                'healthChecks' => MarathonEntityUtils::convertToArrayOfClass(HealthCheck::class),
                'upgradeStrategy' => MarathonEntityUtils::convertToClass(UpgradeStrategy::class),
                'ipAddress' => MarathonEntityUtils::convertToClass(IpAddress::class),
                'env' => MarathonEntityUtils::convertToSortedObject(),
                'labels' => MarathonEntityUtils::convertToSortedObject(),

                # don't skip assigning these just because they are arrays or objects in $dataArray
                'constraints' => MarathonEntityUtils::dontConvert(),
                'args' => MarathonEntityUtils::dontConvert(),
                'uris' => MarathonEntityUtils::dontConvert(),
                'acceptedResourceRoles' => MarathonEntityUtils::dontConvert(),
                'dependencies' => MarathonEntityUtils::dontConvert()
            )
        );

        if (!isset($dataArray['upgradeStrategy'])) {
            $this->upgradeStrategy = new UpgradeStrategy();
        }

        if (!isset($dataArray['labels'])) {
            $this->upgradeStrategy = (object) [];
        }
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize()
    {
        $return = (array) $this;

        // delete empty fields
        $return = array_filter(
            $return,
            function($value) {
                return !is_null($value) || empty($value);
            }
        );

        if (isset($return['networks'])
            && count($return['networks']) == 1 // you can only have one bridge or host network
            && $return['networks'][0]->mode != 'container')
        {
            $return['networks'][0] = (array) $return['networks'][0];
            unset($return['networks'][0]['name']); // only "container" networks can have names
        }

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
