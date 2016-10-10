<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon;

class Docker
{
    public $image = "";

    public $network = "";

    /**
     * @var DockerPortMapping[]
     */
    public $portMappings = [];


    public $privileged = false;

    /**
     * @var DockerParameter
     */
    public $parameters = [];
}