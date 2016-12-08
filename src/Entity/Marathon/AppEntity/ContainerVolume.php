<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

class ContainerVolume extends BaseSubEntity
{
    const DIC = self::class;

    public $containerPath = "";

    public $hostPath = "";

    public $mode = "";

    public function __construct($mData)
    {
        $this->setAllPossibleProperties($mData);
    }

}