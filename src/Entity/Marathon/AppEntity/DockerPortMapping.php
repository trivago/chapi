<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:13
 */
namespace Chapi\Entity\Marathon\AppEntity;

class DockerPortMapping extends BaseSubEntity
{
    const DIC = self::class;

    public $containerPort = 0;

    public $hostPort = 0;

    public $servicePort = 0;

    public $protocol = "";

    public function __construct($oData)
    {
        $this->setAllPossibleProperties($oData);
    }

}