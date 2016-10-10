<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon;

class Container
{
    public $type = "";

    /**
     * @var Docker
     */
    public $docker = null;


    /**
     * @var ContainerVolume[]
     */
    public $volumes = [];
}