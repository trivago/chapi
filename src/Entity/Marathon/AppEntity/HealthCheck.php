<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\HealthCheckCommand;
use Chapi\Entity\Marathon\MarathonEntityUtils;

class HealthCheck
{

    const DIC = self::class;

    public $protocol = '';

    public $path = '';

    public $gracePeriodSeconds = 0;

    public $intervalSeconds = 0;

    public $portIndex = 0;

    public $port = 0;

    public $timeoutSeconds = 20;

    public $maxConsecutiveFailures = 0;

    /**
     * @var HealthCheckCommand
     */
    public $command = null;

    public function __construct($aData = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($aData, $this);

        if (isset($aData['command']))
        {
            $this->command = new HealthCheckCommand((array) $aData['command']);
        }
    }

}