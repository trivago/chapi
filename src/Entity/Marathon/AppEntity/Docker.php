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

class Docker
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

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties(
            $data,
            $this,
            array(
                'parameters' => MarathonEntityUtils::convArrayOfClass(DockerParameters::class)
            )
        );
    }
}
