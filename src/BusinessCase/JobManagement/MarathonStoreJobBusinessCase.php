<?php
/**
 *
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-03
 *
 */

namespace Chapi\BusinessCase\JobManagement;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

class MarathonStoreJobBusinessCase extends AbstractStoreJobBusinessCase implements StoreJobBusinessCaseInterface
{
    /**
     * MarathonStoreJobBusinessCase constructor.
     * @param JobIndexServiceInterface $oJobIndexService
     * @param JobRepositoryInterface $oJobRepositoryRemote
     * @param JobRepositoryInterface $oJobRepositoryLocal
     * @param JobComparisonInterface $oJobComparisonBusinessCase
     * @param LoggerInterface $oLogger
     */
    public function __construct(
        JobIndexServiceInterface $oJobIndexService,
        JobRepositoryInterface $oJobRepositoryRemote,
        JobRepositoryInterface $oJobRepositoryLocal,
        JobComparisonInterface $oJobComparisonBusinessCase,
        LoggerInterface $oLogger
    ) {
        $this->oJobIndexService = $oJobIndexService;
        $this->oLogger = $oLogger;
        $this->oJobComparisonBusinessCase = $oJobComparisonBusinessCase;
        $this->oJobRepositoryRemote = $oJobRepositoryRemote;
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
    }

    /**
     * @return void
     */
    public function storeIndexedJobs()
    {
        $_aRemoteMissingApps = $this->oJobComparisonBusinessCase->getRemoteMissingJobs();
        foreach ($_aRemoteMissingApps as $_sAppId) {
            $this->addRemoteMissingApp($_sAppId);
        }

        $_aLocalMissingApps = $this->oJobComparisonBusinessCase->getLocalMissingJobs();
        foreach ($_aLocalMissingApps as $_sAppId) {
            $this->removeLocalMissingAppInRemote($_sAppId);
        }
        $_aLocalUpdates = $this->oJobComparisonBusinessCase->getLocalJobUpdates();
        foreach ($_aLocalUpdates as $_sAppId) {
            $this->updateAppInRemote($_sAppId);
        }
    }

    /**
     * @param string $sAppId
     * @return bool
     */
    private function addRemoteMissingApp($sAppId)
    {
        if ($this->oJobIndexService->isJobInIndex($sAppId)) {
            /** @var MarathonAppEntity $_oJobEntityLocal */
            $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($sAppId);

            if (!$_oJobEntityLocal instanceof MarathonAppEntity) {
                throw new \RuntimeException('Encountered entity that is not MarathonAppEntity');
            }

            // check if dependency is satisfied
            if ($_oJobEntityLocal->isDependencyJob()) {
                try {
                    $circular = $this->isDependencyCircular($_oJobEntityLocal, count($_oJobEntityLocal->dependencies));
                    if ($circular) {
                        $this->oLogger->error(sprintf(
                            'The dependency for %s is circular. Please fix them.',
                            $sAppId
                        ));
                        return false;
                    }
                } catch (\Exception $e) {
                    $this->oLogger->error(sprintf(
                        'Job %s cannot be added to remote : %s',
                        $sAppId,
                        $e->getMessage()
                    ));
                    return false;
                }


                foreach ($_oJobEntityLocal->dependencies as $_sDependencyKey) {
                    $_bAdded = $this->addRemoteMissingApp($_sDependencyKey);

                    if (!$_bAdded) {
                        $this->oLogger->error(sprintf(
                            'Job "%s" is dependent on "%s" which is missing. Please add them and try again.',
                            $sAppId,
                            $_sDependencyKey
                        ));
                        $this->oJobIndexService->removeJob($_sDependencyKey);
                        return false;
                    }
                }
            }

            if ($this->oJobRepositoryRemote->addJob($_oJobEntityLocal)) {
                $this->oJobIndexService->removeJob($_oJobEntityLocal->getKey());
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully added to marathon',
                    $_oJobEntityLocal->getKey()
                ));

                return true;
            }
            $this->oLogger->error(sprintf(
                'Failed to add job "%s" to marathon',
                $_oJobEntityLocal->getKey()
            ));
        }
        return false;
    }

    /**
     * @param array $arr
     * @return bool
     */
    private function hasDuplicates($arr)
    {
        return !(count($arr) == count(array_unique($arr)));
    }

    /**
     * @param MarathonAppEntity $oEntity
     * @param int $iImmediateChildren
     * @param array $path
     * @return bool
     * @throws \Exception
     */
    private function isDependencyCircular(MarathonAppEntity $oEntity, $iImmediateChildren, &$path = [])
    {
        // Invariant: path will not have duplicates for acyclic dependency tree
        if ($this->hasDuplicates($path)) {
            return true;
        }

        // if we hit leaf (emptyarray), and have no
        // cycle yet, then remove the leaf and return false
        // removing leaf will help maintain a proper path from root to leaf
        // For tree : A ---> B ---> D
        //                      |-> C
        // When we reach node D, path will be [A, B, D]
        // so we pop off D so that the next append will properly show [A, B, C] (legit path)
        if (empty($oEntity->dependencies)) {
            array_pop($path);
            return false;
        }

        foreach ($oEntity->dependencies as $_sDependency) {
            // add this key in path as we will explore its child now
            $path[] = $oEntity->getKey();

            /** @var MarathonAppEntity $_oDependEntity */
            $_oDependEntity = $this->oJobRepositoryLocal->getJob($_sDependency);

            if (!$_oDependEntity) {
                throw new \Exception(sprintf('Dependency chain on non-existing app "%s"', $_sDependency));
            }

            if (!$_oDependEntity instanceof MarathonAppEntity) {
                throw new \RuntimeException('Expected MarathonAppEntity. Found something else');
            }


            // check if dependency has cycle
            if ($this->isDependencyCircular($_oDependEntity, count($_oDependEntity->dependencies), $path)) {
                return true;
            }

            // tracking immediateChildren, this helps us with
            // removing knowing when to pop off key for intermediary dependency
            // For tree: A ---> B ---> D
            //              |      |-> C
            //              |->E
            // for B intermediate Child will be 2.
            // when we process D, it will be reduced to 1 and with C to 0
            // then we will pop B to generate path [A, E] when we reach E.
            $iImmediateChildren--;
            if ($iImmediateChildren == 0) {
                array_pop($path);
            }
        }

        return false;
    }

    /**
     * @param string $sAppId
     * @return bool
     */
    private function removeLocalMissingAppInRemote($sAppId)
    {
        if ($this->oJobIndexService->isJobInIndex($sAppId)) {
            if ($this->oJobRepositoryRemote->removeJob($sAppId)) {
                $this->oJobIndexService->removeJob($sAppId);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully removed from marathon',
                    $sAppId
                ));

                return true;
            }
            $this->oLogger->error(sprintf(
                'Failed to remove"%s" from marathon',
                $sAppId
            ));
        }
        return false;
    }

    /**
     * @param string $sAppId
     * @return bool
     */
    private function updateAppInRemote($sAppId)
    {
        if ($this->oJobIndexService->isJobInIndex($sAppId)) {
            $_oUpdatedConfig = $this->oJobRepositoryLocal->getJob($sAppId);
            $_bAddedBack = $this->oJobRepositoryRemote->updateJob($_oUpdatedConfig);

            // updated
            if ($_bAddedBack) {
                $this->oJobIndexService->removeJob($sAppId);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully updated in marathon',
                    $sAppId
                ));

                return true;
            }

            $this->oLogger->error(sprintf(
                'Failed to update job "%s" in marathon',
                $sAppId
            ));
        }

        return false;
    }
}
