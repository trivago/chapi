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
    private $jobRepositoryLocal;

    /**
     * @var JobRepositoryInterface
     */
    private $jobRepositoryChronos;

    /**
     * @var array
     */
    private $jobTreeLocal = [];

    /**
     * @var array
     */
    private $jobTreeChronos = [];

    /**
     * @param JobRepositoryInterface $jobRepositoryLocal
     * @param JobRepositoryInterface $jobRepositoryChronos
     */
    public function __construct(
        JobRepositoryInterface $jobRepositoryLocal,
        JobRepositoryInterface $jobRepositoryChronos
    ) {
        $this->jobRepositoryLocal = $jobRepositoryLocal;
        $this->jobRepositoryChronos = $jobRepositoryChronos;
    }

    /**
     * @param string $jobName
     * @param int $repository
     * @return string[]
     */
    public function getChildJobs($jobName, $repository)
    {
        $jobTree = ($repository == self::REPOSITORY_LOCAL) ? $this->getJobTreeLocal() : $this->getJobTreeChronos();
        return (isset($jobTree[$jobName])) ? $jobTree[$jobName] : [];
    }

    /**
     * @param string $jobName
     * @param int $repository
     * @return bool
     */
    public function hasChildJobs($jobName, $repository)
    {
        $_aJobs = $this->getChildJobs($jobName, $repository);
        return (!empty($_aJobs));
    }

    /**
     * @return array
     */
    private function getJobTreeLocal()
    {
        if (empty($this->jobTreeLocal)) {
            $this->initJobTree($this->jobTreeLocal, $this->jobRepositoryLocal);
        }

        return $this->jobTreeLocal;
    }

    /**
     * @return array
     */
    private function getJobTreeChronos()
    {
        if (empty($this->jobTreeChronos)) {
            $this->initJobTree($this->jobTreeChronos, $this->jobRepositoryChronos);
        }

        return $this->jobTreeChronos;
    }

    /**
     * @param array $jobTree
     * @param JobRepositoryInterface $jobRepository
     * @return void
     */
    private function initJobTree(&$jobTree, JobRepositoryInterface $jobRepository)
    {
        // reset job tree in case of reloading
        $jobTree = [];

        /** @var ChronosJobEntity $jobEntity */
        foreach ($jobRepository->getJobs() as $jobEntity) {
            if ($jobEntity->isDependencyJob()) {
                foreach ($jobEntity->parents as $parentJobName) {
                    // init parent in tree
                    if (!isset($jobTree[$parentJobName])) {
                        $jobTree[$parentJobName] = [];
                    }
                    // set job as children to parent
                    $jobTree[$parentJobName][] = $jobEntity->name;
                }
            }
        }
    }
}
