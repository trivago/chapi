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
    public static function setPropertyIfExist($source, $target, $property)
    {
        if (isset($source[$property]) &&
            property_exists($target, $property)) {
            $target->{$property} = $source[$property];
        }
    }

    /**
     * Sets all possible properties in the class from oData.
     * If the type is array or object, then it is ignored.
     * @param $data
     */
    public static function setAllPossibleProperties($data, $target)
    {
        foreach ($data as $attributeName => $attributeValue) {
            // dont set array or objects.
            // Because this would need further type information to properly set.
            if (is_array($attributeValue) || is_object($attributeValue)) {
                continue;
            }
            self::setPropertyIfExist($data, $target, $attributeName);
        }
    }
}
