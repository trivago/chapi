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

class IpAddress implements \JsonSerializable
{
    const DIC = self::class;

    public $groups = [];

    public $labels = null;

    public $networkName = '';

    public $unknownFields = [];

    public function __construct($data = [])
    {
        $this->unknownFields = MarathonEntityUtils::setAllPossibleProperties(
            $data,
            $this,
            [
                'groups' => MarathonEntityUtils::convArray(),
                'labels' => MarathonEntityUtils::convObject()
            ]
        );
    }

    public function jsonSerialize()
    {
        $return = (array) $this;

        $return += $this->unknownFields;
        unset($return['unknownFields']);

        return $return;
    }
}
