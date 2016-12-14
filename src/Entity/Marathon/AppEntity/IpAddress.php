<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;


use Chapi\Entity\Marathon\MarathonEntityUtils;

class IpAddress
{
    const DIC = self::class;

    public $groups = [];

    public $labels = [];

    public $networkName = "";

    public function __construct($oData)
    {
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);

        if(property_exists($oData, "groups"))
        {
            $this->groups = $oData->groups;
        }
        if (property_exists($oData, "labels"))
        {
            $this->labels = $oData->labels;
        }
    }
}