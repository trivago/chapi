<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 13/02/17
 * Time: 15:18
 */

namespace unit\Entity\Marathon\AppEntity;

use Chapi\Entity\Marathon\AppEntity\UpgradeStrategy;

class UpgradeStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testAllKeysAreCorrect()
    {
        $keys = ["minimumHealthCapacity", "maximumOverCapacity"];

        $upgradeStrategy = new UpgradeStrategy();

        foreach ($keys as $property) {
            $this->assertObjectHasAttribute($property, $upgradeStrategy);
        }
    }

    public function testUpgradeStrategySetCorrectly()
    {
        $data = [
            "minimumHealthCapacity" => 2,
            "maximumOverCapacity" => 3
        ];
        $upgradeStrategy = new UpgradeStrategy($data);

        $this->assertEquals(2, $upgradeStrategy->minimumHealthCapacity);
        $this->assertEquals(3, $upgradeStrategy->maximumOverCapacity);
    }

    public function testUnknownFieldsInUpgradeStrategy()
    {
        $jobEntity = new UpgradeStrategy([
            'unique_field' => "I feel like it's 2005",
            'unique_array' => ['unique', 'values']
        ]);

        $jobEntityJson = json_encode($jobEntity);
        $jobEntityTest = json_decode($jobEntityJson);

        $this->assertTrue(property_exists($jobEntityTest, 'unique_field'));
        $this->assertAttributeEquals(['unique', 'values'], 'unique_array', $jobEntityTest);

        $this->assertFalse(property_exists($jobEntityTest, 'unknownFields'));
    }
}
