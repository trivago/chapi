<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

class HealthCheckCommand extends BaseSubEntity
{
    const DIC = self::class;

    public $value = "";

    public function __construct($oData)
    {
        $this->setAllPossibleProperties($oData);
    }
}