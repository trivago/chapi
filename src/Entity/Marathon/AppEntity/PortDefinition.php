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

    public function __construct($aData = [])
    {
        MarathonEntityUtils::setAllPossibleProperties((array) $aData, $this);
        if (isset($aData['labels']))
        {
            $this->labels = (object) $aData['labels'];
        } else {
            $this->labels = (object) [];
        }

    }

    /**
     * @inheritdoc
     */
    function jsonSerialize()
    {
        $_aRet = (array) $this;
        $_aRet = array_filter($_aRet, function($v, $k) {
            return !is_null($v);
        }, ARRAY_FILTER_USE_BOTH);
        return $_aRet;
    }
}