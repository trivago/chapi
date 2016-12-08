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

class Docker extends BaseSubEntity
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
        $this->setAllPossibleProperties($oData);

        if (property_exists($oData, "portMappings"))
        {
            foreach($oData->portMappings as $portMapping)
            {
                $this->portMappings[] = new DockerPortMapping($portMapping);
            }
        }

        if (property_exists($oData, "parameters"))
        {
            foreach($oData->parameters as $parameter)
            {
                $this->parameters[] = new DockerParameters($parameter);
            }
        }

    }
}