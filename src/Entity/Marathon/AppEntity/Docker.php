<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
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

    public function __construct($oData)
    {
        if ($oData == null)
        {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);

        if (isset($oData["portMappings"]))
        {
            foreach($oData["portMappings"] as $portMapping)
            {
                $this->portMappings[] = new DockerPortMapping((array)$portMapping);
            }
        }

        if (isset($oData["parameters"]))
        {
            foreach($oData["parameters"] as $parameter)
            {
                $this->parameters[] = new DockerParameters((array)$parameter);
            }
        }

    }
}