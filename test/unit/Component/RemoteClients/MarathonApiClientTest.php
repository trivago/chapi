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
use ChapiTest\src\TestTraits\AppEntityTrait;
use Prophecy\Argument;

class MarathonApiClientTest extends \PHPUnit_Framework_TestCase
{
    use AppEntityTrait;
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oHttpClient;

    /**  @var \Prophecy\Prophecy\ObjectProphecy */
    private $oHttpResponse;

    public function setUp()
    {
        $this->oHttpClient = $this->prophesize('Chapi\Component\Http\HttpClientInterface');
        $this->oHttpResponse = $this->prophesize('Chapi\Component\Http\HttpClientResponseInterface');
    }

    public function testListingJobsSuccess()
    {
        $this->oHttpResponse
            ->getStatusCode()
            ->willReturn(200);

        $this->oHttpResponse
            ->json()
            ->willReturn(["id" => "/someid"]);

        $this->oHttpClient
            ->get(Argument::exact('/v2/apps'))
            ->willReturn($this->oHttpResponse->reveal());

        $oMarathonApiClient = new MarathonApiClient($this->oHttpClient->reveal());

        $aData = $oMarathonApiClient->listingJobs();

        $this->assertEquals(["id" => "/someid"], $aData);
    }

    public function testAddingJobSuccess()
    {
        $oAppEntity = $this->getValidMarathonAppEntity('/some/id');

        $this->oHttpResponse
            ->getStatusCode()
            ->willReturn(201);

        $this->oHttpClient
            ->postJsonData(Argument::exact('/v2/apps'), Argument::exact($oAppEntity))
            ->willReturn($this->oHttpResponse->reveal());

        $oMarathonApiClient = new MarathonApiClient($this->oHttpClient->reveal());


        $this->assertTrue($oMarathonApiClient->addingJob($oAppEntity));
    }

    public function testUpdatingJobSuccess()
    {
        $oAppEntity = $this->getValidMarathonAppEntity('/some/id');

        $this->oHttpResponse
            ->getStatusCode()
            ->willReturn(200);

        $this->oHttpClient
            ->putJsonData(Argument::exact('/v2/apps'), Argument::exact($oAppEntity))
            ->willReturn($this->oHttpResponse->reveal());

        $oMarathonApiClient = new MarathonApiClient($this->oHttpClient->reveal());

        $this->assertTrue($oMarathonApiClient->updatingJob($oAppEntity));
    }

    public function testRemoveJobSuccess()
    {
        $this->oHttpResponse
            ->getStatusCode()
            ->willReturn(200);

        $this->oHttpClient
            ->delete(Argument::exact('/v2/apps/someid'))
            ->willReturn($this->oHttpResponse->reveal());

        $oMarathonApiClient = new MarathonApiClient($this->oHttpClient->reveal());

        $this->assertTrue($oMarathonApiClient->removeJob('someid'));
    }


    public function testPingSuccess()
    {
        $this->oHttpClient
            ->get(Argument::exact('/v2/info'))
            ->willReturn($this->oHttpResponse);

        $oMarathonApiClient = new MarathonApiClient($this->oHttpClient->reveal());

        $this->assertTrue($oMarathonApiClient->ping());
    }

    public function testPingFailure()
    {
        $this->oHttpClient
            ->get(Argument::exact('/v2/info'))
            ->willThrow(new \Exception());

        $oMarathonApiClient = new MarathonApiClient($this->oHttpClient->reveal());

        $this->assertFalse($oMarathonApiClient->ping());
    }

}
