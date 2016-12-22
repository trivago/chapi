<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;
use Chapi\Entity\Marathon\AppEntity\ContainerVolume;
use Chapi\Entity\Marathon\AppEntity\Docker;

class Container
{
    const DIC = self::class;

    public $type = "";

    /**
     * @var Docker
     */
    public $docker = null;


    /**
     * @var ContainerVolume[]
     */
    public $volumes = [];

    public function __construct($mData)
    {
        if ($mData == null)
        {
            return;
        }

        MarathonEntityUtils::setAllPossibleProperties($mData, $this);

        if (isset($mData["docker"]))
        {
            $this->docker = new Docker((array)$mData["docker"]);
        }

        if (isset($mData["volumes"]))
        {
            foreach ($mData["volumes"] as $volume)
            {
                $this->volumes[] = new ContainerVolume((array)$volume);
            }
        }

    }
}