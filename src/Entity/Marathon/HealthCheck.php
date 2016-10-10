<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon;

class HealthCheck
{

    public $protocol = "";

    public $path = "";

    public $gracePeriodSeconds = 0;

    public $intervalSeconds = 0;

    public $portIndex = 0;

    public $port = 0;

    public $timeoutSeconds = 0;

    public $maxConsecutiveFailures = 0;

    /**
     * @var HealthCheckCommand
     */
    public $command = null;

}