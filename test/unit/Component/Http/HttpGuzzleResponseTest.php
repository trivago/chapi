<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-08
 */

namespace unit\Component\Http;

use Chapi\Component\Http\HttpGuzzleResponse;

class HttpGuzzleResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $responseInterface;

    public function setUp()
    {
        $this->responseInterface = $this->prophesize('Psr\Http\Message\ResponseInterface');
    }

    public function testGetStatusCodeSuccess()
    {
        $this->responseInterface
            ->getStatusCode()
            ->shouldBeCalledTimes(1)
            ->willReturn(200);

        $httpGuzzleResponse = new HttpGuzzleResponse($this->responseInterface->reveal());

        $this->assertEquals(200, $httpGuzzleResponse->getStatusCode());
    }

    public function testGetBodySuccess()
    {
        $this->responseInterface
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn('string');

        $httpGuzzleResponse = new HttpGuzzleResponse($this->responseInterface->reveal());

        $this->assertEquals('string', $httpGuzzleResponse->getBody());
    }

    public function testJsonSuccess()
    {
        $this->responseInterface
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn(json_encode([1,2,3]));

        $httpGuzzleResponse = new HttpGuzzleResponse($this->responseInterface->reveal());

        $this->assertEquals([1,2,3], $httpGuzzleResponse->json());
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
        $this->responseInterface
            ->getBody()
            ->shouldBeCalledTimes(1)
            ->willReturn(json_encode($dummyBody));

        $httpGuzzleResponse = new HttpGuzzleResponse($this->responseInterface->reveal());

        $result = $httpGuzzleResponse->json();
        $this->assertInternalType('array', $result);
        $this->assertEquals($dummyBody, $result);
    }
}
