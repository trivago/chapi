<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-12-08
 *
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