<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-12-08
 *
 */

namespace Chapi\Entity\Marathon;

use Chapi\Entity\Marathon\AppEntity;
use Chapi\Entity\Marathon\AppEntity\Container;
use Chapi\Entity\Marathon\AppEntity\FetchUrl;
use Chapi\Entity\Marathon\AppEntity\HealthCheck;
use Chapi\Entity\Marathon\AppEntity\PortDefinition;
use Chapi\Entity\Marathon\AppEntity\UpgradeStrategy;
use SebastianBergmann\Comparator\ExceptionComparatorTest;

class AppSubEntityFactory implements AppSubEntityFactoryInterface
{
    private static $sSubEntityMap = [
        "portDefinition" => PortDefinition::DIC,
        "container" => Container::DIC,
        "fetch" => FetchUrl::DIC,
        "healthChecks" => HealthCheck::DIC,
        "upgradeStrategy" => UpgradeStrategy::DIC,
        "ipAddress" => IpAddress::DIC
    ];

    /**
     * @param $sName
     * @param $mData
     * @return array
     * @throws \Exception
     */
    public function getSubEntity($sName, $mData)
    {
        if (!array_key_exists($sName, self::$sSubEntityMap))
        {
            throw new \Exception(sprintf("sub-entity %s not found for marathon app configuration", $sName));
        }

        if (is_array($mData))
        {
            $ret = [];
            foreach($mData as $data)
            {
                $ret[] = new self::$sSubEntityMap[$sName]($data);
            }
            return $ret;
        }
        return new self::$sSubEntityMap[$sName]($mData);
    }
}