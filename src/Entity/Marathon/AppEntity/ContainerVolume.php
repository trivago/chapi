<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;

class ContainerVolume
{
    const DIC = self::class;

    public $containerPath = "";

    public $hostPath = "";

    public $mode = "";

    public function __construct($oData)
    {
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);
    }

}