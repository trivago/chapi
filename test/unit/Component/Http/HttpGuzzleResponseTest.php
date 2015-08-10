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
        $this->oResponseInterface = $this->prophesize('GuzzleHttp\Message\ResponseInterface');
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
            ->json()
            ->shouldBeCalledTimes(1)
            ->willReturn([1,2,3]);

        $_oHttpGuzzleResponse = new HttpGuzzlResponse($this->oResponseInterface->reveal());

        $this->assertEquals([1,2,3], $_oHttpGuzzleResponse->json());
    }
}