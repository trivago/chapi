<?php
/**
 * Created by PhpStorm.
 * User: sbrueggen
 * Date: 17.04.18
 * Time: 15:56
 */

namespace Chapi\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\MarathonEntityUtils;


class Fetch
{

    public $uri = "";

    public $extract = true;

    public $executable = false;

    public $cache = false;

    public function __construct($data = [])
    {
        MarathonEntityUtils::setAllPossibleProperties($data, $this);
    }

}