<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:13
 */
namespace Chapi\Entity\Marathon\AppEntity;

class PortDefinition extends BaseSubEntity
{
    const DIC = self::class;
    public $port = 0;

    public $protocol = "";

    public $name = "";

    public $labels = [];

    public function __construct($oData)
    {
        $this->setAllPossibleProperties($oData);
        if(property_exists($oData, "labels"))
        {
            $this->labels = $oData->labels;
        }
    }
}