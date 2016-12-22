<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:13
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;

class PortDefinition
{
    const DIC = self::class;
    public $port = 0;

    public $protocol = "";

    public $name = "";

    public $labels = [];

    public function __construct($oData)
    {
        if ($oData == null)
        {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);
        if(isset($oData["labels"]))
        {
            $this->labels = $oData["labels"];
        }
    }
}