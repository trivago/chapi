<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-07
 *
 */


namespace unit\Component\Http;


use Chapi\Component\Http\HttpGuzzlClient;
use Chapi\Entity\Http\AuthEntity;
use Prophecy\Argument;

class HttpGuzzlClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oGuzzelClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oGuzzlResponse;

    public function setUp()
    {
        $this->oGuzzelClient = $this->prophesize('GuzzleHttp\ClientInterface');
        $this->oGuzzlResponse = $this->prophesize('GuzzleHttp\Message\ResponseInterface');
    }

    public function testGetSuccess()
    {
        $_sUrl = '/url/for/test';

        $_oAuthEntitiy = new AuthEntity("", "");
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT
        ];

        $this->oGuzzelClient->get(Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oGuzzlResponse->reveal())
        ;

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $_oHttpGuzzlClient->get($_sUrl));
    }

    public function testGetSuccessWithHttpBasicAuth()
    {
        $_sUrl = '/url/for/test';
        $_aAuth = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $_oAuthEntitiy = new AuthEntity($_aAuth['username'], $_aAuth['password']);
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT,
            'auth' => [$_aAuth['username'], $_aAuth['password']]
        ];

        $this->oGuzzelClient->get(Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oGuzzlResponse->reveal())
        ;

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $_oResponse = $_oHttpGuzzlClient->get($_sUrl);
        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $_oResponse);
    }

    /**
     * @expectedException \Chapi\Exception\HttpConnectionException
     */
    public function testGetFailure()
    {
        $_sUrl = '/url/for/test';

        $_oAuthEntitiy = new AuthEntity("", "");
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT
        ];

        $this->oGuzzelClient->get(Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception('test exception'))
        ;

        $this->oGuzzelClient->getBaseUrl()
            ->shouldBeCalledTimes(1)
            ->willReturn('http://www.abc.com')
        ;

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $this->assertNull($_oHttpGuzzlClient->get($_sUrl));
    }

    public function testPostJsonDataSuccess()
    {
        $_sUrl = '/url/for/test';
        $_aPostData = ['data' => [1, 2, 3]];
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT,
            'json' => $_aPostData
        ];
        $_oAuthEntitiy = new AuthEntity("", "");
        $_oRequestInterface = $this->prophesize('GuzzleHttp\Message\RequestInterface');

        $this->oGuzzelClient->createRequest(Argument::exact('post'), Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($_oRequestInterface->reveal())
        ;

        $this->oGuzzelClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oGuzzlResponse->reveal())
        ;

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $_oHttpGuzzlClient->postJsonData($_sUrl, $_aPostData));
    }

    public function testPostJsonDataSuccessWithHttpBasicAuth()
    {
        $_sUrl = '/url/for/test';
        $_aAuth = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $_oAuthEntitiy = new AuthEntity($_aAuth['username'], $_aAuth['password']);
        $_aPostData = ['data' => [1, 2, 3]];
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT,
            'json' => $_aPostData,
            'auth' => [$_aAuth['username'], $_aAuth['password']]
        ];

        $_oRequestInterface = $this->prophesize('GuzzleHttp\Message\RequestInterface');

        $this->oGuzzelClient->createRequest(Argument::exact('post'), Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($_oRequestInterface->reveal());

        $this->oGuzzelClient->send(Argument::type('GuzzleHttp\Message\RequestInterface'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oGuzzlResponse->reveal());

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $_oHttpGuzzlClient->postJsonData($_sUrl, $_aPostData));
    }

    public function testDeleteSuccess()
    {
        $_sUrl = '/url/for/test';
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT,
        ];
        $_oAuthEntitiy = new AuthEntity("", "");
        $this->oGuzzelClient->delete(Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oGuzzlResponse->reveal())
        ;

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $_oHttpGuzzlClient->delete($_sUrl));
    }

    public function testDeleteSuccessWithHttpBasicAuth()
    {
        $_sUrl = '/url/for/test';
        $_aAuth = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $_oAuthEntitiy = new AuthEntity($_aAuth['username'], $_aAuth['password']);
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzlClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzlClient::DEFAULT_TIMEOUT,
            'auth' => [$_aAuth['username'], $_aAuth['password']]
        ];

        $this->oGuzzelClient->delete(Argument::exact($_sUrl), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->oGuzzlResponse->reveal())
        ;

        $_oHttpGuzzlClient = new HttpGuzzlClient($this->oGuzzelClient->reveal(), $_oAuthEntitiy);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $_oHttpGuzzlClient->delete($_sUrl));
    }
}