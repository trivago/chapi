<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 16/02/17
 * Time: 11:13
 */

namespace unit\Component\RemoteClients;

use Chapi\Component\RemoteClients\MarathonApiClient;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Exception\HttpConnectionException;
use ChapiTest\src\TestTraits\AppEntityTrait;
use Prophecy\Argument;

class MarathonApiClientTest extends \PHPUnit\Framework\TestCase
{
    use AppEntityTrait;
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $httpClient;

    /**  @var \Prophecy\Prophecy\ObjectProphecy */
    private $httpResponse;

    public function setUp()
    {
        $this->httpClient = $this->prophesize('Chapi\Component\Http\HttpClientInterface');
        $this->httpResponse = $this->prophesize('Chapi\Component\Http\HttpClientResponseInterface');
    }

    public function testListingJobsSuccess()
    {
        $this->httpResponse
            ->getStatusCode()
            ->willReturn(200);

        $this->httpResponse
            ->json()
            ->willReturn(["id" => "/someid"]);

        $this->httpClient
            ->get(Argument::exact('/v2/apps'))
            ->willReturn($this->httpResponse->reveal());

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $data = $marathonApiClient->listingJobs();

        $this->assertEquals(["id" => "/someid"], $data);
    }

    public function testAddingJobSuccess()
    {
        $appEntity = $this->getValidMarathonAppEntity('/some/id');

        $this->httpResponse
            ->getStatusCode()
            ->willReturn(201);

        $this->httpClient
            ->postJsonData(Argument::exact('/v2/apps'), Argument::exact($appEntity))
            ->willReturn($this->httpResponse->reveal());

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());


        $this->assertTrue($marathonApiClient->addingJob($appEntity));
    }

    public function testUpdatingJobSuccess()
    {
        $appEntity = $this->getValidMarathonAppEntity('/some/id');

        $this->httpResponse
            ->getStatusCode()
            ->willReturn(200);

        $this->httpClient
            ->putJsonData(Argument::exact('/v2/apps//some/id'), Argument::exact($appEntity))
            ->willReturn($this->httpResponse->reveal());

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertTrue($marathonApiClient->updatingJob($appEntity));
    }

    public function testRemoveJobSuccess()
    {
        $this->httpResponse
            ->getStatusCode()
            ->willReturn(200);

        $this->httpClient
            ->delete(Argument::exact('/v2/apps/someid'))
            ->willReturn($this->httpResponse->reveal());

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertTrue($marathonApiClient->removeJob('someid'));
    }


    public function testPingSuccess()
    {
        $this->httpClient
            ->get(Argument::exact('/v2/info'))
            ->willReturn($this->httpResponse);

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertTrue($marathonApiClient->ping());
    }

    public function testPingFailureForConnectError()
    {
        $this->httpClient
            ->get(Argument::exact('/v2/info'))
            ->willThrow(new HttpConnectionException("somemessage", HttpConnectionException::ERROR_CODE_CONNECT_EXCEPTION));

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertFalse($marathonApiClient->ping());
    }

    public function testPingFailureForRequestError()
    {
        $this->httpClient
            ->get(Argument::exact('/v2/info'))
            ->willThrow(new HttpConnectionException("somemessage", HttpConnectionException::ERROR_CODE_REQUEST_EXCEPTION));

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertFalse($marathonApiClient->ping());
    }

    public function testPingSucessFor4xxErrors()
    {
        $this->httpClient
            ->get(Argument::exact('/v2/info'))
            ->willThrow(new HttpConnectionException("somemessage", 403));

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertTrue($marathonApiClient->ping());
    }

    public function testPingSuccessFor5xxErrors()
    {
        $this->httpClient
            ->get(Argument::exact('/v2/info'))
            ->willThrow(new HttpConnectionException("somemessage", 501));

        $marathonApiClient = new MarathonApiClient($this->httpClient->reveal());

        $this->assertTrue($marathonApiClient->ping());
    }
}
