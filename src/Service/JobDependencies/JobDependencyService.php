<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-10
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace Chapi\Service\JobDependencies;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;

class JobDependencyService implements JobDependencyServiceInterface
{
    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryLocal;

    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryChronos;

    /**
     * @var array
     */
    private $aJobTreeLocal = [];

    /**
     * @var array
     */
    private $aJobTreeChronos = [];

    /**
     * @param JobRepositoryInterface $oJobRepositoryLocal
     * @param JobRepositoryInterface $oJobRepositoryChronos
     */
    public function __construct(
        JobRepositoryInterface $oJobRepositoryLocal,
        JobRepositoryInterface $oJobRepositoryChronos
    )
    {
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
    }

    /**
     * @param $sJobName
     * @param $iRepository
     * @return string[]
     */
    public function getChildJobs($sJobName, $iRepository)
    {
        $_aJobTree = ($iRepository == self::REPOSITORY_LOCAL) ? $this->getJobTreeLocal() : $this->getJobTreeChronos();
        return (isset($_aJobTree[$sJobName])) ? $_aJobTree[$sJobName] : [];
    }

    /**
     * @param $sJobName
     * @param $iRepository
     * @return bool
     */
    public function hasChildJobs($sJobName, $iRepository)
    {
        $_aJobs = $this->getChildJobs($sJobName, $iRepository);
        return (!empty($_aJobs));
    }

    /**
     * @return array
     */
    private function getJobTreeLocal()
    {
        if (empty($this->aJobTreeLocal))
        {
            $this->initJobTree($this->aJobTreeLocal, $this->oJobRepositoryLocal);
        }

        return $this->aJobTreeLocal;
    }

    /**
     * @return array
     */
    private function getJobTreeChronos()
    {
        if (empty($this->aJobTreeChronos))
        {
            $this->initJobTree($this->aJobTreeChronos, $this->oJobRepositoryChronos);
        }

        return $this->aJobTreeChronos;
    }

    /**
     * @param array $aJobTree
     * @param JobRepositoryInterface $oJobRepository
     * @return void
     */
    private function initJobTree(&$aJobTree, JobRepositoryInterface $oJobRepository)
    {
        // reset job tree in case of reloading
        $aJobTree = [];

        /** @var ChronosJobEntity $_oJobEntity */
        foreach ($oJobRepository->getJobs() as $_oJobEntity)
        {
            if ($_oJobEntity->isDependencyJob())
            {
                foreach ($_oJobEntity->parents as $_sParentJobName)
                {
                    // init parent in tree
                    if (!isset($aJobTree[$_sParentJobName]))
                    {
                        $aJobTree[$_sParentJobName] = [];
                    }
                    // set job as children to parent
                    $aJobTree[$_sParentJobName][] = $_oJobEntity->name;
                }
            }
        }
    }
}