<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 10/10/16
 * Time: 19:14
 */
namespace Chapi\Entity\Marathon\AppEntity;

class FetchUrl extends BaseSubEntity
{
    const DIC = self::class;

    public $uri = "";

    public $executable = false;

    public $extract = false;

    public $cache = false;

    public function __construct($oData)
    {
        $this->setAllPossibleProperties($oData);
    }
}