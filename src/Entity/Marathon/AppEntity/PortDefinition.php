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

class PortDefinition implements \JsonSerializable
{
    const DIC = self::class;
    public $port = 0;

    public $protocol = 'tcp';

    public $name = null;

    public $labels = null;

    public $unknownFields = [];

    public function __construct($data = [])
    {
        $this->unknownFields = MarathonEntityUtils::setAllPossibleProperties(
            (array) $data,
            $this,
            ['labels' => MarathonEntityUtils::convObject()]
        );

        if (!isset($data['labels'])) {
            $this->labels = (object) [];
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $return = (array) $this;

        $return += $this->unknownFields;
        unset($return['unknownFields']);

        $return = array_filter($return, function ($value, $key) {
            return !is_null($value);
        }, ARRAY_FILTER_USE_BOTH);

        return $return;
    }
}
