<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */

namespace Chapi\Service\JobRepository;

use Chapi\Component\Cache\CacheInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Exception\JobLoadException;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Glob;

class BridgeFileSystem implements BridgeInterface
{
    /**
     * @var Filesystem
     */
    private $fileSystemService;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $repositoryDir = '';

    /**
     * @var string[]
     */
    private $directorySeparators = ['.', ':', '-', '\\'];

    /**
     * @var array
     */
    private $jobFileMap = [];

    /**
     * @var array
     */
    private $groupedApps = [];

    /**
     * @param Filesystem $oFileSystemService
     * @param CacheInterface $cache
     * @param string $repositoryDir
     */
    public function __construct(
        Filesystem $oFileSystemService,
        CacheInterface $cache,
        $repositoryDir
    ) {
        $this->fileSystemService = $oFileSystemService;
        $this->cache = $cache;
        $this->repositoryDir = $repositoryDir;
    }

    /**
     * @return JobEntityInterface[]
     */
    public function getJobs()
    {
        if (empty($this->jobFileMap)) {
            $jobFiles = $this->getJobFilesFromFileSystem($this->repositoryDir);
            return $this->loadJobsFromFileContent($jobFiles, true);
        }
        return $this->loadJobsFromFileContent($this->jobFileMap, false);
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     * @throws JobLoadException
     */
    public function addJob(JobEntityInterface $jobEntity)
    {
        // generate job file path by name
        $jobFile = $this->generateJobFilePath($jobEntity);

        if ($this->hasDumpFile($jobFile, $jobEntity)) {
            $this->setJobFileToMap($jobEntity->getKey(), $jobFile);
            return true;
        }

        return false;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $jobEntity)
    {
        if (in_array($jobEntity->getKey(), $this->groupedApps)) {
            // marathon's group case where app belongs to a group file
            return $this->dumpFileWithGroup(
                $this->getJobFileFromMap($jobEntity->getKey()),
                $jobEntity
            );
        }
        return $this->hasDumpFile(
            $this->getJobFileFromMap($jobEntity->getKey()),
            $jobEntity
        );
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $jobEntity)
    {
        if (in_array($jobEntity->getKey(), $this->groupedApps)) {
            $jobFile = $this->getJobFileFromMap($jobEntity->getKey());
            $this->dumpFileWithGroup(
                $jobFile,
                $jobEntity,
                false
            );

            unset($this->jobFileMap[$jobEntity->getKey()]);
            return true;
        }

        $jobFile = $this->getJobFileFromMap($jobEntity->getKey());
        $this->fileSystemService->remove($jobFile);

        return $this->hasUnsetJobFileFromMap($jobEntity->getKey(), $jobFile);
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return string
     */
    private function generateJobFilePath(JobEntityInterface $jobEntity)
    {
        if ($jobEntity->getEntityType() == JobEntityInterface::CHRONOS_TYPE) {
            $jobPath = str_replace(
                $this->directorySeparators,
                DIRECTORY_SEPARATOR,
                $jobEntity->getKey()
            );
        } else {
            $jobPath = $jobEntity->getKey();
        }

        return $this->repositoryDir . DIRECTORY_SEPARATOR . $jobPath . '.json';
    }

    /**
     * @param string $path
     * @param array $jobFiles
     * @return array
     */
    private function getJobFilesFromFileSystem($path, array &$jobFiles = [])
    {
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Path "%s" is not valid', $path));
        }

        $temp = Glob::glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*');

        foreach ($temp as $path) {
            if (is_file($path) && preg_match('~\.json~i', $path)) {
                $jobFiles[] = $path;
            } elseif (is_dir($path)) {
                $this->getJobFilesFromFileSystem($path, $jobFiles);
            }
        }

        return $jobFiles;
    }

    /**
     * @param string $jobName
     * @param string $jobFile
     * @throws JobLoadException
     */
    private function setJobFileToMap($jobName, $jobFile)
    {
        // set path to job file map
        if (isset($this->jobFileMap[$jobName])) {
            throw new JobLoadException(
                sprintf('The jobname "%s" already exists. Jobnames have to be unique - Please check your local jobfiles for duplicate entries.', $jobName),
                JobLoadException::ERROR_CODE_DUPLICATE_JOB_ID
            );
        }

        $this->jobFileMap[$jobName] = $jobFile;
    }

    /**
     * @param string $jobName
     * @return string
     * @throws \RuntimeException
     */
    private function getJobFileFromMap($jobName)
    {
        if (!isset($this->jobFileMap[$jobName])) {
            throw new \RuntimeException(sprintf('Can\'t find file for job "%s"', $jobName));
        }
        return $this->jobFileMap[$jobName];
    }

    /**
     * @param string $jobName
     * @param string $jobFile
     * @return bool
     * @throws \RuntimeException
     */
    private function hasUnsetJobFileFromMap($jobName, $jobFile = '')
    {
        $jobFile = (!empty($jobFile)) ? $jobFile : $this->getJobFileFromMap($jobName);
        if (file_exists($jobFile)) {
            throw new \RuntimeException(sprintf('Job file "%s" for job "%s" still exists.', $jobFile, $jobName));
        }

        // unset path from job file map
        unset($this->jobFileMap[$jobName]);
        return true;
    }

    /**
     * @param array $jobFiles
     * @param bool $setToFileMap
     * @return JobEntityInterface[]
     * @throws JobLoadException
     */
    private function loadJobsFromFileContent(array $jobFiles, $setToFileMap)
    {
        $jobs = [];

        foreach ($jobFiles as $jobFilePath) {
            $jobEntities = [];
            // remove comment blocks
            $temp = json_decode(
                preg_replace(
                    '~\/\*(.*?)\*\/~mis',
                    '',
                    file_get_contents($jobFilePath)
                )
            );

            if ($temp) {
                // chronos
                if (property_exists($temp, 'name')) {
                    $jobEntities[] = new ChronosJobEntity($temp);
                } //marathon
                elseif (property_exists($temp, 'id')) {
                    foreach ($this->getMarathonEntitiesForConfig($temp) as $app) {
                        $jobEntities[] = $app;
                    }
                } else {
                    throw new JobLoadException(
                        'Could not distinguish job as either chronos or marathon',
                        JobLoadException::ERROR_CODE_UNKNOWN_ENTITY_TYPE
                    );
                }

                /** @var JobEntityInterface $jobEntity */
                foreach ($jobEntities as $jobEntity) {
                    if ($setToFileMap) {
                        // set path to job file map
                        $this->setJobFileToMap($jobEntity->getKey(), $jobFilePath);
                    }

                    $jobs[] = $jobEntity;
                }
            } else {
                throw new JobLoadException(
                    sprintf('Unable to load json job data from "%s". Please check if the json is valid.', $jobFilePath),
                    JobLoadException::ERROR_CODE_NO_VALID_JSON
                );
            }
        }

        return $jobs;
    }


    private function getMarathonEntitiesForConfig($entityData)
    {
        $return = [];
        if (property_exists($entityData, 'apps')) {
            // store individual apps like single apps
            foreach ($entityData->apps as $app) {
                $groupEntity = new MarathonAppEntity($app);
                $this->groupedApps[] = $app->id;
                $return[] = $groupEntity;
            }
        } else {
            $return[] = new MarathonAppEntity($entityData);
        }
        return $return;
    }

    /**
     * @param string $jobFile
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    private function hasDumpFile($jobFile, JobEntityInterface $jobEntity)
    {
        $this->fileSystemService->dumpFile(
            $jobFile,
            json_encode($jobEntity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return (file_exists($jobFile));
    }

    /**
     * @param string $jobFile
     * @param JobEntityInterface $jobEntity
     * @param bool $add
     * @return bool
     */
    private function dumpFileWithGroup($jobFile, JobEntityInterface $jobEntity, $add = true)
    {
        $groupConfig = file_get_contents($jobFile);

        $decodedConfig = json_decode(preg_replace(
            '~\/\*(.*?)\*\/~mis',
            '',
            $groupConfig
        ));

        if (!property_exists($decodedConfig, 'apps')) {
            throw new \RuntimeException(sprintf(
                'Job file %s does not contain group configuration. But, "%s" belongs to group %s',
                $jobFile,
                $jobEntity->getKey(),
                $decodedConfig->id
            ));
        }

        $appFound = false;
        foreach ($decodedConfig->apps as $key => $app) {
            if ($app->id == $jobEntity->getKey()) {
                if (!$add) {
                    array_splice($decodedConfig->apps, $key, 1);
                    if (count($decodedConfig->apps) == 0) {
                        $this->fileSystemService->remove($jobFile);
                        $iIndex = array_search($jobEntity->getKey(), $this->groupedApps);
                        if ($iIndex) {
                            unset($this->groupedApps[$iIndex]);
                        }
                        return false;
                    }
                } else {
                    $decodedConfig->apps[$key] = $jobEntity;
                }
                $appFound = true;
            }
        }

        if (!$appFound) {
            throw new \RuntimeException(sprintf(
                'Could update job. job %s could not be found in the group file %s.',
                $jobEntity->getKey(),
                $jobFile
            ));
        }

        $updatedConfig = json_encode($decodedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->fileSystemService->dumpFile(
            $jobFile,
            $updatedConfig
        );

        return (file_exists($jobFile));
    }
}
