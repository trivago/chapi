<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;

class UpgradeStrategy
{
    const DIC = self::class;

    public $minimumHealthCapacity = 1;

    public $maximumOverCapacity = 1;

    public function __construct($oData = null)
    {
        if ($oData == null)
        {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);
    }
}