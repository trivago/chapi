<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 08/12/16
 * Time: 18:31
 */

namespace Chapi\Entity\Marathon\AppEntity;


class BaseSubEntity
{
    public function setPropertyIfExist($oSource, $sProperty)
    {
        if (property_exists($oSource, $sProperty))
        {
            $this->{$sProperty} = $oSource->{$sPropety};
        }
    }

    /**
     * Sets all possible properties in the class from oData.
     * If the type is array or object, then it is ignored.
     * @param $oData
     */
    public function setAllPossibleProperties($oData)
    {
        foreach($oData as $attrName => $attrValue)
        {
            // dont set array or objects.
            // Because this would need further type information to properly set.
            if (is_array($attrValue) || is_object($attrValue)) {
                continue;
            }

            $this->setPropertyIfExist($oData, $attrName);
        }
    }
}