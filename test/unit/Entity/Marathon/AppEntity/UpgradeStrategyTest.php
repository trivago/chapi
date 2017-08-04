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
        $_aKeys = ["minimumHealthCapacity", "maximumOverCapacity"];

        $oUpgradeStrategy = new UpgradeStrategy();

        foreach ($_aKeys as $sProperty) {
            $this->assertObjectHasAttribute($sProperty, $oUpgradeStrategy);
        }
    }

    public function testUpgradeStrategySetCorrectly()
    {
        $aData = [
            "minimumHealthCapacity" => 2,
            "maximumOverCapacity" => 3
        ];
        $oUpgradeStrategy = new UpgradeStrategy($aData);

        $this->assertEquals(2, $oUpgradeStrategy->minimumHealthCapacity);
        $this->assertEquals(3, $oUpgradeStrategy->maximumOverCapacity);
    }
}
