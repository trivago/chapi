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
use Chapi\Entity\Marathon\AppEntity\ContainerVolume;
use Chapi\Entity\Marathon\AppEntity\Docker;

class Container
{
    const DIC = self::class;

    public $type = '';

    /**
     * @var Docker
     */
    public $docker = null;


    /**
     * @var ContainerVolume[]
     */
    public $volumes = [];

    public function __construct($aData = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($aData, $this);

        if (isset($aData['docker']))
        {
            $this->docker = new Docker((array)$aData['docker']);
        }

        if (isset($aData['volumes']))
        {
            foreach ($aData['volumes'] as $volume)
            {
                $this->volumes[] = new ContainerVolume((array)$volume);
            }
        }

    }
}