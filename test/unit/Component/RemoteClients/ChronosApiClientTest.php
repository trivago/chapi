<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-04
 *
 */

namespace unit\Component\RemoteClients;

use Chapi\Component\RemoteClients\ChronosApiClient;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Exception\HttpConnectionException;
use Prophecy\Argument;

class ChronosApiClientTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $httpClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $httpResponse;

    /**
     *
     */
    public function setUp()
    {
        $this->httpClient = $this->prophesize('Chapi\Component\Http\HttpClientInterface');
        $this->httpResponse = $this->prophesize('Chapi\Component\Http\HttpClientResponseInterface');
    }

    // --------------------
    // listingJobs tests
    // --------------------
    public function testListingJobsSuccess()
    {
        $testResult = ['entity1', 'entity2'];

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(200);
        $this->httpResponse->json()->shouldBeCalledTimes(1)->willReturn($testResult);

        $this->httpClient->get(Argument::exact('/scheduler/jobs'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertEquals($testResult, $apiClient->listingJobs());
    }

    public function testListingJobsFailure()
    {
        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);
        $this->httpResponse->json()->shouldNotBeCalled();

        $this->httpClient->get(Argument::exact('/scheduler/jobs'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertEquals([], $apiClient->listingJobs());
    }

    // --------------------
    // addingJob tests
    // --------------------
    public function testAddingScheduleJobSuccess()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($apiClient->addingJob($testJobEntity));
    }

    public function testAddingDependencyJobSuccess()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->parents = ['jobA', 'jobB'];

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($apiClient->addingJob($testJobEntity));
    }

    public function testAddingScheduleJobFailure()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($apiClient->addingJob($testJobEntity));
    }

    public function testAddingDependencyJobFailure()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->parents = ['jobA', 'jobB'];

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($apiClient->addingJob($testJobEntity));
    }

    /**
     * @expectedException \Chapi\Exception\ApiClientException
     */
    public function testAddingJobFailure()
    {
        $testJobEntity = new ChronosJobEntity();

        $this->httpClient->postJsonData()
            ->shouldNotBeCalled()
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertNull($apiClient->addingJob($testJobEntity));
    }

    // --------------------
    // updatingJob tests
    // --------------------
    public function testUpdatingScheduleJobSuccess()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($apiClient->updatingJob($testJobEntity));
    }

    public function testUpdatingDependencyJobSuccess()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->parents = ['jobA', 'jobB'];

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($apiClient->updatingJob($testJobEntity));
    }

    public function testUpdatingScheduleJobFailure()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->schedule = 'R/2015-07-07T01:00:00Z/P1D';

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/iso8601'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($apiClient->updatingJob($testJobEntity));
    }

    public function testUpdatingDependencyJobFailure()
    {
        $testJobEntity = new ChronosJobEntity();
        $testJobEntity->parents = ['jobA', 'jobB'];

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->httpClient->postJsonData(Argument::exact('/scheduler/dependency'), Argument::exact($testJobEntity))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($apiClient->updatingJob($testJobEntity));
    }

    /**
     * @expectedException \Chapi\Exception\ApiClientException
     */
    public function testUpdatingJobFailure()
    {
        $testJobEntity = new ChronosJobEntity();

        $this->httpClient->postJsonData()
            ->shouldNotBeCalled()
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertNull($apiClient->updatingJob($testJobEntity));
    }

    // --------------------
    // removeJob tests
    // --------------------
    public function testRemoveJobSuccess()
    {
        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(204);

        $this->httpClient->delete(Argument::exact('/scheduler/job/jobName'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($apiClient->removeJob('jobName'));
    }

    public function testRemoveJobFailure()
    {
        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);

        $this->httpClient->delete(Argument::exact('/scheduler/job/jobName'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($apiClient->removeJob('jobName'));
    }

    public function testGetJobStatsSuccess()
    {
        $testResult = [
            'histogram' => [
                '75thPercentile' => 2.34,
                '95thPercentile' => 1.23,
                '98thPercentile' => 1.23,
                '99thPercentile' => 1.23,
                'median' => 11.11,
                'mean' => 2.22,
                'count' => 10
            ],
            'taskStatHistory' => []
        ];

        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(200);
        $this->httpResponse->json()->shouldBeCalledTimes(1)->willReturn($testResult);

        $this->httpClient->get(Argument::exact('/scheduler/job/stat/JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertEquals($testResult, $apiClient->getJobStats('JobA'));
    }

    public function testGetJobStatsFailure()
    {
        $this->httpResponse->getStatusCode()->shouldBeCalledTimes(1)->willReturn(500);
        $this->httpResponse->json()->shouldNotBeCalled();

        $this->httpClient->get(Argument::exact('/scheduler/job/stat/JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->httpResponse->reveal())
        ;

        $apiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertEquals([], $apiClient->getJobStats('JobA'));
    }

    public function testPingSuccess()
    {
        $this->httpClient
            ->get(Argument::exact('/scheduler/jobs'))
            ->willReturn($this->httpResponse);

        $chronosApiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($chronosApiClient->ping());
    }

    public function testPingFailureForConnectError()
    {
        $this->httpClient
            ->get(Argument::exact('/scheduler/jobs'))
            ->willThrow(new HttpConnectionException("somemessage", HttpConnectionException::ERROR_CODE_CONNECT_EXCEPTION));

        $chronosApiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($chronosApiClient->ping());
    }

    public function testPingFailureForRequestError()
    {
        $this->httpClient
            ->get(Argument::exact('/scheduler/jobs'))
            ->willThrow(new HttpConnectionException("somemessage", HttpConnectionException::ERROR_CODE_REQUEST_EXCEPTION));

        $chronosApiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertFalse($chronosApiClient->ping());
    }

    public function testPingSucessFor4xxErrors()
    {
        $this->httpClient
            ->get(Argument::exact('/scheduler/jobs'))
            ->willThrow(new HttpConnectionException("somemessage", 403));

        $chronosApiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($chronosApiClient->ping());
    }

    public function testPingSuccessFor5xxErrors()
    {
        $this->httpClient
            ->get(Argument::exact('/scheduler/jobs'))
            ->willThrow(new HttpConnectionException("somemessage", 501));

        $chronosApiClient = new ChronosApiClient($this->httpClient->reveal());

        $this->assertTrue($chronosApiClient->ping());
    }
}
