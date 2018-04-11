<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */
namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;

class ContainerVolume implements \JsonSerializable
{
    const DIC = self::class;

    public $containerPath = '';

    public $hostPath = '';

    public $mode = '';

    public $unknownFields = [];

    public function __construct($data = [])
    {
        $this->unknownFields = MarathonEntityUtils::setAllPossibleProperties($data, $this);
    }

    public function jsonSerialize()
    {
        $return = (array) $this;

        $return += $this->unknownFields;
        unset($return['unknownFields']);

        return $return;
    }
}
