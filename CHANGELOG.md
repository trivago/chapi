# dev-master (v0.5.x)
    2015-11-25 msiebeneicher <marc.siebeneicher@trivago.com>
        * Updating dependencies
            - Updating doctrine/cache (v1.4.4 => v1.5.1)
        * Improved list command output
            - added "onlyDisabled" option
            - added additional information "disabled", "error rate" and "errors since last success"

    2015-11-24 msiebeneicher <marc.siebeneicher@trivago.com>
        * [issue#41] - Provided possibility to handle two different chronos job repositories
        
        * Updating dependencies (including require-dev)
            - Updating symfony/console (v2.7.5 => v2.7.7)
            - Updating symfony/dependency-injection (v2.7.5 => v2.7.7)
            - Updating symfony/filesystem (v2.7.5 => v2.7.7)
            - Updating symfony/config (v2.7.5 => v2.7.7)
            - Updating symfony/monolog-bridge (v2.7.5 => v2.7.7)
            - Updating symfony/event-dispatcher (v2.7.5 => v2.7.7)
            - Updating doctrine/cache (v1.4.2 => v1.4.4)
            - Updating symfony/yaml (v2.7.5 => v2.7.7)
            - Updating phpunit/phpunit (4.8.14 => 4.8.18)    
        * Updated CommandTestTrait for symfony console change
    
    2015-10-26 msiebeneicher <marc.siebeneicher@trivago.com>
        * Added own exception if local jobs are unable to load in case of invalid json strings
    
    2015-10-22 msiebeneicher <marc.siebeneicher@trivago.com>
        * Update dependencies
          - Installing sebastian/global-state (1.1.1)
          - Installing monolog/monolog (1.17.2)

# v0.4.0
    2015-10-10 msiebeneicher <marc.siebeneicher@trivago.com>
        * Update dependencies

    2015-09-25 msiebeneicher <marc.siebeneicher@trivago.com>
        * Improved epsilon validation [issue#36]
        * added own Iso8601Entity and refactor deprecated usage of parseIso8601String()
        * removed deprecated method parseIso8601String() [BC]

    2015-09-20 msiebeneicher <marc.siebeneicher@trivago.com>
        * [issue#17] - Added optional parameters to configure command
        * [issue#13] - Added validation command
        * Improved AbstractCommand by adding CommandUtils

# v0.3.0
    2015-09-15 msiebeneicher <marc.siebeneicher@trivago.com>
        * Added "epsilon" validation for JobEntityValidatorService

    2015-09-13 msiebeneicher <marc.siebeneicher@trivago.com>
        * Job storage now check dependencies for non scheduled jobs [issue#4]
        * added hasJob() method to JobRepositoryInterface

    2015-09-12 msiebeneicher <marc.siebeneicher@trivago.com>
        * added naming validation [issue#25]

    2015-09-10 msiebeneicher <marc.siebeneicher@trivago.com>
        * created a scheduling overview [issue#24]

    2015-09-09 msiebeneicher <marc.siebeneicher@trivago.com>
        * added getJobStats() to ApiClient
        * added separate JobEntityInterface
          * added isSchedulingJob() to JobEntity
          * added isDependencyJob() to JobEntity
        * added JobStatsService and JobDependencyService

    2015-08-28 msiebeneicher <marc.siebeneicher@trivago.com>
        * renamed JobRepositoryServiceInterface to JobRepositoryInterface
        * refactored JobRepository (separation of concerns, add bridge interface, reduced complexity, updated unit tests)

    2015-08-27 msiebeneicher <marc.siebeneicher@trivago.com>
        * added scrutinizer

    2015-08-26 msiebeneicher <marc.siebeneicher@trivago.com>
        * [issue#18] added unit tests for StoreJobBusinessCase

    2015-08-23 msiebeneicher <marc.siebeneicher@trivago.com>
        * [issue#18] added logger to StoreJobBusinessCase
        * [issue#18] update verbosity map

    2015-08-19 msiebeneicher <marc.siebeneicher@trivago.com>
        * fix issue to compare boolean values

# v0.2.0
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
