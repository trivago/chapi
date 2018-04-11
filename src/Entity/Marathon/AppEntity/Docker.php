<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\DockerParameters;
use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;
use Chapi\Entity\Marathon\MarathonEntityUtils;

class Docker implements \JsonSerializable
{
    const DIC = self::class;

    public $image = '';

    public $network = '';

    /**
     * @var DockerPortMapping[]
     */
    public $portMappings = [];


    public $privileged = false;

    public $forcePullImage = false;

    /**
     * @var DockerParameters
     */
    public $parameters = [];

    public $unknownFields = [];

    public function __construct($data = [])
    {
        $this->unknownFields = MarathonEntityUtils::setAllPossibleProperties(
            $data,
            $this,
            array(
                'portMappings' => MarathonEntityUtils::convArrayOfClass(DockerPortMapping::class),
                'parameters' => MarathonEntityUtils::convArrayOfClass(DockerParameters::class)
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
