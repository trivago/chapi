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

class DockerPortMapping implements \JsonSerializable
{
    const DIC = self::class;

    public $containerPort = 0;

    public $hostPort = 0;

    public $servicePort = 0;

    public $protocol = 'tcp';

    public $name = null;

    public $unknownFields = [];

    public function __construct($data = [])
    {
        $this->unknownFields = MarathonEntityUtils::setAllPossibleProperties($data, $this);
    }

    public static function less(DockerPortMapping $left, DockerPortMapping $right)
    {
        if ($left->containerPort != $right->containerPort) {
            return $left->containerPort - $right->containerPort;
        }

        if ($left->hostPort != $right->hostPort) {
            return $left->hostPort - $right->hostPort;
        }

        if ($left->servicePort != $right->servicePort) {
            return $left->servicePort - $right->servicePort;
        }

        if ($left->protocol != $right->protocol) {
            return strcmp($left->protocol, $right->protocol);
        }

        return strcmp($left->name, $right->name);
    }

    public function jsonSerialize()
    {
        $return = (array) $this;

        $return += $this->unknownFields;
        unset($return['unknownFields']);

        return $return;
    }
}
