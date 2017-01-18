<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-10-16
 *
 */
namespace Chapi\Entity\Marathon;


class MarathonEntityUtils
{
    public static function setPropertyIfExist($oSource, $oTarget, $sProperty)
    {
        if (isset($oSource[$sProperty]) &&
            property_exists($oTarget, $sProperty))
        {
            $oTarget->{$sProperty} = $oSource[$sProperty];
        }
    }

    /**
     * Sets all possible properties in the class from oData.
     * If the type is array or object, then it is ignored.
     * @param $oData
     */
    public static function setAllPossibleProperties($oData, $oTarget)
    {
        foreach($oData as $attrName => $attrValue)
        {
            // dont set array or objects.
            // Because this would need further type information to properly set.
            if (is_array($attrValue) || is_object($attrValue)) {
                continue;
            }

            MarathonEntityUtils::setPropertyIfExist($oData, $oTarget, $attrName);
        }
    }
}