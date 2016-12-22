<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:13
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;

class DockerPortMapping
{
    const DIC = self::class;

    public $containerPort = 0;

    public $hostPort = 0;

    public $servicePort = 0;

    public $protocol = "tcp";

    public function __construct($oData)
    {
        if ($oData == null)
        {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);
    }

}