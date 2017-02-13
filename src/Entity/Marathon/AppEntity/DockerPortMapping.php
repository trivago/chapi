<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
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

    public $name = "";

    public function __construct($aData = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($aData, $this);
    }

}