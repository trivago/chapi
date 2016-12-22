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
        if ($oData == null)
        {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);

        if(isset($oData["groups"]))
        {
            $this->groups = $oData["groups"];
        }
        if (isset($oData["labels"]))
        {
            $this->labels = $oData["labels"];
        }
    }
}