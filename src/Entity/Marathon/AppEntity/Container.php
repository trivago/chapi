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
     * @var DockerPortMapping[]
     */
    public $portMappings = [];


    /**
     * @var ContainerVolume[]
     */
    public $volumes = [];

    /**
     * Container constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {

        MarathonEntityUtils::setAllPossibleProperties(
            $data,
            $this,
            array(
                'portMappings' => MarathonEntityUtils::convertToArrayOfClass(DockerPortMapping::class),
                'docker' => MarathonEntityUtils::convertToClass(Docker::class),
                'volumes' => MarathonEntityUtils::convertToArrayOfClass(ContainerVolume::class)
            )
        );
    }
}
