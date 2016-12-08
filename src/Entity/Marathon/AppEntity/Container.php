<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\ContainerVolume;
use Chapi\Entity\Marathon\AppEntity\Docker;

class Container extends BaseSubEntity
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

    public function __construct($oData)
    {
        $this->setAllPossibleProperties($oData);

        $this->docker = new Docker($oData->docker);

        foreach ($oData->volumes as $volume)
        {
            $this->volumes[] = new ContainerVolume($volume);
        }
    }
}