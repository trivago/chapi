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

class HealthCheck implements \JsonSerializable
{

    const DIC = self::class;

    public $protocol = 'HTTP';

    public $path = '/';

    public $gracePeriodSeconds = 300;

    public $intervalSeconds = 60;

    public $portIndex = 0;

    public $port = null;

    public $timeoutSeconds = 20;

    public $maxConsecutiveFailures = 3;

    /**
     * @var HealthCheckCommand
     */
    public $command = null;

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($data, $this);

        if (isset($data['command'])) {
            $this->command = new HealthCheckCommand((array) $data['command']);
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $return = (array) $this;
        if (is_null($this->port)) {
            unset($return["port"]);
        }
        return $return;
    }
}
