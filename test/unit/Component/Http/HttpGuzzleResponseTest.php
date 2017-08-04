<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-08
 */

namespace unit\Component\Http;

use Chapi\Component\Http\HttpGuzzlResponse;

class HttpGuzzleResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oResponseInterface;

    public function setUp()
    {
        $this->oResponseInterface = $this->prophesize('Psr\Http\Message\ResponseInterface');
    }

    public function testGetStatusCodeSuccess()
    {
        $this->oResponseInterface
            ->getStatusCode()
            ->shouldBeCalledTimes(1)
            ->willReturn(200);

        $_oHttpGuzzleResponse = new HttpGuzzlResponse($this->oResponseInterface->reveal());

        $this->assertEquals(200, $_oHttpGuzzleResponse->getStatusCode());
    }

    public function testGetBodySuccess()
    {
        $this->oResponseInterface
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn('string');

        $_oHttpGuzzleResponse = new HttpGuzzlResponse($this->oResponseInterface->reveal());

        $this->assertEquals('string', $_oHttpGuzzleResponse->getBody());
    }

    public function testJsonSuccess()
    {
        $this->oResponseInterface
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn(json_encode([1,2,3]));

        $_oHttpGuzzleResponse = new HttpGuzzlResponse($this->oResponseInterface->reveal());

        $this->assertEquals([1,2,3], $_oHttpGuzzleResponse->json());
    }

    public function testJsonReturnAssocArray()
    {
        $dummyBody = [
            'histogram' => [
                '75thPercentile' => 539159,
                '95thPercentile' => 539159,
                '98thPercentile' => 539159,
                '99thPercentile' => 539159,
                'median' => 539159,
                'count' => 15
            ],
            'taskStatHistory' => []
        ];
        $this->oResponseInterface
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn(json_encode($dummyBody));

        $_oHttpGuzzleResponse = new HttpGuzzlResponse($this->oResponseInterface->reveal());

        $result = $_oHttpGuzzleResponse->json();
        $this->assertInternalType('array', $result);
        $this->assertEquals($dummyBody, $result);
    }
}
