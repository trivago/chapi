<?php
/**
 * Created by PhpStorm.
 * User: sbrueggen
 * Date: 17.04.18
 * Time: 16:09
 */

namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;


class Network
{
    public $mode = "";

    public $name = "";

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($data, $this);
    }
}