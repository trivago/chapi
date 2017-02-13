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

    public $image = "";

    public $network = "";

    /**
     * @var DockerPortMapping[]
     */
    public $portMappings = [];


    public $privileged = false;

    /**
     * @var DockerParameters
     */
    public $parameters = [];

    public function __construct($aData = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($aData, $this);

        if (isset($aData["portMappings"]))
        {
            foreach($aData["portMappings"] as $portMapping)
            {
                $this->portMappings[] = new DockerPortMapping((array)$portMapping);
            }
        }

        if (isset($aData["parameters"]))
        {
            foreach($aData["parameters"] as $parameter)
            {
                $this->parameters[] = new DockerParameters((array)$parameter);
            }
        }

    }
}