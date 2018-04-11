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

class UpgradeStrategy implements \JsonSerializable
{
    const DIC = self::class;

    public $minimumHealthCapacity = 1;

    public $maximumOverCapacity = 1;

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
