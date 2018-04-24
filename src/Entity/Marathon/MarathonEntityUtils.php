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
            return true;
        }
        return false;
    }

    /**
     * Sets all possible properties in the class from $data.
     * If the type is array or object, then it is ignored if there is no conversion in $conversion_map.
     * @param $data
     * @param $target
     * @param $conversionMap
     *
     * @return array all fields in $data that weren't stored in $target
     */
    public static function setAllPossibleProperties($data, $target, $conversionMap = [])
    {
        $unknownProperties = [];
        foreach ($data as $attributeName => $attributeValue) {
            // Don't set array or objects if no conversion method is specified.
            // Because this would need further type information to properly set.
            if (isset($conversionMap[$attributeName])) {
                $data[$attributeName] = $conversionMap[$attributeName]($attributeValue);
            }
            if (!self::setPropertyIfExist($data, $target, $attributeName)) {
                $unknownProperties[$attributeName] = $attributeValue;
            }
        }
        return $unknownProperties;
    }

    /**
     * This is useful if you don't want an array or object to be skipped by setAllPossibleProperties().
     */
    public static function dontConvert() {
        return function($data) {
            return $data;
        };
    }

    public static function convertToArray() {
        return function($data) {
            return (array) $data;
        };
    }

    public static function convertToObject() {
        return function($data) {
            return (object) $data;
        };
    }

    /**
     * This is usefull for shorter and stable diff output.
     */
    public static function convertToSortedObject() {
        return function($data) {
            $a = (array) $data; // ksort is inplace, so we need a copy
            ksort($a);
            return (object) $a;
        };
    }

    public static function convertToClass($class) {
        return function($data) use ($class) {
            return new $class((array) $data);
        };
    }

    public static function convertToArrayOfClass($class) {
        return function($data) use ($class) {
            $array = [];
            if ($data !== null) {
                foreach ($data as $item) {
                    $array[] = new $class((array) $item);
                }
            }
            return $array;
        };
    }
}
