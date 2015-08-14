# dev-master (v0.1.x)
    2015-08-14 msiebeneicher <marc.siebeneicher@trivago.com>
        * improve schedule comparison in JobComparisonBusinessCase
        * update and fix unit tests
        * added logger (debug) to JobComparisonBusinessCase
    
    2015-08-13 msiebeneicher <marc.siebeneicher@trivago.com>
        * added scheduleTimeZone comparison to JobComparisonBusinessCase
        * sub one interval for date period creation in DatePeriodFactory
        * added JobComparisonBusinessCaseTest
        * added DatePeriodFactoryTest
    
    2015-08-11 msiebeneicher <marc.siebeneicher@trivago.com>
        * added JobRepositoryChronosTest and JobEntityValidatorServiceTest
        * [issue#3] - return failed validation output for commits
            - added psr logger and dic configuration
            - updated JobEntityValidatorService
            - updated JobRepositoryChronos
            - updated unit tests
            - added psr/log and symfony/monolog-bridge dependencies 

    2015-08-08 msiebeneicher <marc.siebeneicher@trivago.com>
        * added HttpGuzzleResponseTest
        * fix issue in JobIndexService to reset job index
        * added JobIndexServiceTest
            
    2015-08-07 msiebeneicher <marc.siebeneicher@trivago.com>
        * added HttpGuzzlClientTest

    2015-08-05 msiebeneicher <marc.siebeneicher@trivago.com>
        * [issue#6] - Invalid cache after adding, updating or removing in JobRepositoryChronos
        * added JobEntity unit tests

# v0.1.1
    2015-08-04 msiebeneicher <marc.siebeneicher@trivago.com>
        * updated ApiClient::addingJob() and integrated unit tests

    2015-08-03 msiebeneicher <marc.siebeneicher@trivago.com>
        * added first DoctrineCacheTest and travis-ci config
        * changed default parameters in ChapiApplication
        * updated docs

# v0.1.0
    2015-08-03 msiebeneicher <marc.siebeneicher@trivago.com>
        * released first stable version
            
