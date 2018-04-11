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

class Container implements \JsonSerializable
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

    public $unknownFields = [];

    /**
     * Container constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {

        $this->unknownFields = MarathonEntityUtils::setAllPossibleProperties(
            $data,
            $this,
            array(
                'docker' => MarathonEntityUtils::convClass(Docker::class),
                'volumes' => MarathonEntityUtils::convArrayOfClass(ContainerVolume::class)
            )
        );
    }

    public function jsonSerialize()
    {
        $return = (array) $this;

        $return += $this->unknownFields;
        unset($return['unknownFields']);

        return $return;
    }
}
