<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 08/12/16
 * Time: 16:10
 */

namespace Chapi\Entity\Marathon;


interface AppSubEntityFactoryInterface
{
    /**
     * @param $sName
     * @param $mData
     * @return mixed
     */
    public function getSubEntity($sName, $mData);
}