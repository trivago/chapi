<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\HealthCheckCommand;
use Chapi\Entity\Marathon\MarathonEntityUtils;
use Symfony\Component\Console\Command\Command;

class HealthCheck
{

    const DIC = self::class;

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

    public function __construct($oData)
    {
        if ($oData == null)
        {
            return;
        }
        MarathonEntityUtils::setAllPossibleProperties($oData, $this);

        if(property_exists($oData, "command"))
        {
            $this->command = new Command($oData->command);
        }
    }

}