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

class FilterIgnoreSettingsTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $chapiConfig;

    public function setUp()
    {
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');

        $this->chapiConfig = $this->prophesize('Chapi\Component\Config\ChapiConfigInterface');
        $this->chapiConfig->getProfileConfig()->willReturn([
            'ignore' => [
                'test-123*',
                '/test/123/*',
                '!test-123-ignore-not'
            ]
        ]);
    }

    public function testIgnoreRules()
    {
        $filter = new FilterIgnoreSettings(
            $this->logger->reveal(),
            $this->chapiConfig->reveal()
        );


        $entity = $this->getValidScheduledJobEntity('test-234');
        $this->assertTrue($filter->isInteresting($entity));

        $entity = $this->getValidScheduledJobEntity('test-123-xyz');
        $this->assertFalse($filter->isInteresting($entity));

        $entity = $this->getValidMarathonAppEntity('/test/234/x');
        $this->assertTrue($filter->isInteresting($entity));

        $entity = $this->getValidMarathonAppEntity('/test/123/xyz');
        $this->assertFalse($filter->isInteresting($entity));
    }
}
