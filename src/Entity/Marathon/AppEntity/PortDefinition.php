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

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties((array) $data, $this);
        if (isset($data['labels'])) {
            $this->labels = (object) $data['labels'];
        } else {
            $this->labels = (object) [];
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $return = (array) $this;
        $return = array_filter($return, function ($value, $key) {
            return !is_null($value);
        }, ARRAY_FILTER_USE_BOTH);
        return $return;
    }
}
