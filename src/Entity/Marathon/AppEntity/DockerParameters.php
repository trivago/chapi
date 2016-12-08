<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:13
 */
namespace Chapi\Entity\Marathon\AppEntity;

class DockerParameters extends BaseSubEntity
{
    const DIC = self::class;

    public $key = "";

    public $value = "";

    public function __construct($oData)
    {
        $this->setAllPossibleProperties($oData);
    }
}