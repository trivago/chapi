<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-01
 *
 */

namespace unit\Service\JobRepository\Filter;

use Chapi\Service\JobRepository\Filter\FilterIgnoreSettings;
use ChapiTest\src\TestTraits\AppEntityTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;

class FilterIgnoreSettingsTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oChapiConfig;

    public function setUp()
    {
        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');

        $this->oChapiConfig = $this->prophesize('Chapi\Component\Config\ChapiConfigInterface');
        $this->oChapiConfig->getProfileConfig()->willReturn([
            'ignore' => [
                'test-123*',
                '/test/123/*',
                '!test-123-ignore-not'
            ]
        ]);
    }

    public function testIgnoreRules()
    {
        $_oFilter = new FilterIgnoreSettings(
            $this->oLogger->reveal(),
            $this->oChapiConfig->reveal()
        );


        $_oEntity = $this->getValidScheduledJobEntity('test-234');
        $this->assertTrue($_oFilter->isInteresting($_oEntity));

        $_oEntity = $this->getValidScheduledJobEntity('test-123-xyz');
        $this->assertFalse($_oFilter->isInteresting($_oEntity));

        $_oEntity = $this->getValidMarathonAppEntity('/test/234/x');
        $this->assertTrue($_oFilter->isInteresting($_oEntity));

        $_oEntity = $this->getValidMarathonAppEntity('/test/123/xyz');
        $this->assertFalse($_oFilter->isInteresting($_oEntity));
    }
}
