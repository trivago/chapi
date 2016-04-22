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
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Exception\JobLoadException;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Glob;

class BridgeFileSystem implements BridgeInterface
{
    /**
     * @var Filesystem
     */
    private $oFileSystemService;

    /**
     * @var CacheInterface
     */
    private $oCache;

    /**
     * @var string
     */
    private $sRepositoryDir = '';

    /**
     * @var string[]
     */
    private $aDirectorySeparators = ['.', ':', '-', '\\'];

    /**
     * @var array
     */
    private $aJobFileMap = [];

    /**
     * @param Filesystem $oFileSystemService
     * @param CacheInterface $oCache
     * @param string $sRepositoryDir
     */
    public function __construct(
        Filesystem $oFileSystemService,
        CacheInterface $oCache,
        $sRepositoryDir
    )
    {
        $this->oFileSystemService = $oFileSystemService;
        $this->oCache = $oCache;
        $this->sRepositoryDir = $sRepositoryDir;
    }

    /**
     * @return JobEntity[]
     */
    public function getJobs()
    {
        if (empty($this->aJobFileMap))
        {
            $_aJobFiles = $this->getJobFilesFromFileSystem($this->sRepositoryDir);
            return $this->loadJobsFromFileContent($_aJobFiles, true);
        }

        return $this->loadJobsFromFileContent($this->aJobFileMap, false);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function addJob(JobEntity $oJobEntity)
    {
        // generate job file path by name
        $_sJobFile = $this->generateJobFilePath($oJobEntity);

        if ($this->hasDumpFile($_sJobFile, $oJobEntity))
        {
            $this->setJobFileToMap($oJobEntity->name, $_sJobFile);
            return true;
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntity $oJobEntity)
    {
        return $this->hasDumpFile(
            $this->getJobFileFromMap($oJobEntity->name),
            $oJobEntity
        );
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntity $oJobEntity)
    {
        $_sJobFile = $this->getJobFileFromMap($oJobEntity->name);
        $this->oFileSystemService->remove($_sJobFile);

        return $this->hasUnsetJobFileFromMap($oJobEntity->name, $_sJobFile);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return string
     */
    private function generateJobFilePath(JobEntity $oJobEntity)
    {
        $_sJobPath = str_replace(
            $this->aDirectorySeparators,
            DIRECTORY_SEPARATOR,
            $oJobEntity->name
        );
        return $this->sRepositoryDir . DIRECTORY_SEPARATOR . $_sJobPath . '.json';
    }

    /**
     * @param string $sPath
     * @param array $aJobFiles
     * @return array
     */
    private function getJobFilesFromFileSystem($sPath, array &$aJobFiles = [])
    {
        if (!is_dir($sPath))
        {
            throw new \RuntimeException(sprintf('Path "%s" is not valid', $sPath));
        }

        $_aTemp = Glob::glob(rtrim($sPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*');

        foreach ($_aTemp as $_sPath)
        {
            if (is_file($_sPath) && preg_match('~\.json~i', $_sPath))
            {
                $aJobFiles[] = $_sPath;
            } elseif (is_dir($_sPath))
            {
                $this->getJobFilesFromFileSystem($_sPath, $aJobFiles);
            }
        }

        return $aJobFiles;
    }

    /**
     * @param string $sJobName
     * @param string $sJobFile
     * @throws JobLoadException
     */
    private function setJobFileToMap($sJobName, $sJobFile)
    {
        // set path to job file map
        if (isset($this->aJobFileMap[$sJobName]))
        {
            throw new JobLoadException(
                sprintf('The jobname "%s" already exists. Jobnames have to be unique - Please check your local jobfiles for duplicate entries.', $sJobName),
                JobLoadException::ERROR_CODE_DUPLICATE_JOB_ID
            );
        }

        $this->aJobFileMap[$sJobName] = $sJobFile;
    }

    /**
     * @param string $sJobName
     * @return string
     * @throws \RuntimeException
     */
    private function getJobFileFromMap($sJobName)
    {
        if (!isset($this->aJobFileMap[$sJobName]))
        {
            throw new \RuntimeException(sprintf('Can\'t find file for job "%s"', $sJobName));
        }

        return $this->aJobFileMap[$sJobName];
    }

    /**
     * @param string $sJobName
     * @param string $sJobFile
     * @return bool
     * @throws \RuntimeException
     */
    private function hasUnsetJobFileFromMap($sJobName, $sJobFile = '')
    {
        $_sJobFile = (!empty($sJobFile)) ? $sJobFile : $this->getJobFileFromMap($sJobName);
        if (file_exists($_sJobFile))
        {
            throw new \RuntimeException(sprintf('Job file "%s" for job "%s" still exists.', $_sJobFile, $sJobName));
        }

        // unset path from job file map
        unset($this->aJobFileMap[$sJobName]);
        return true;
    }

    /**
     * @param array $aJobFiles
     * @param bool $bSetToFileMap
     * @return JobEntity[]
     * @throws JobLoadException
     */
    private function loadJobsFromFileContent(array $aJobFiles, $bSetToFileMap)
    {
        $_aJobs = [];

        foreach ($aJobFiles as $_sJobFilePath)
        {
            // remove comment blocks
            $_aTemp = json_decode(
                preg_replace(
                    '~\/\*(.*?)\*\/~mis',
                    '',
                    file_get_contents($_sJobFilePath)
                )
            );

            if ($_aTemp)
            {
                $_oJobEntity = new JobEntity($_aTemp);
                $_aJobs[] = $_oJobEntity;

                if ($bSetToFileMap)
                {
                    // set path to job file map
                    $this->setJobFileToMap($_oJobEntity->name, $_sJobFilePath);
                }
            }
            else
            {
                throw new JobLoadException(
                    sprintf('Unable to load json job data from "%s". Please check if the json is valid.', $_sJobFilePath),
                    JobLoadException::ERROR_CODE_NO_VALID_JSON
                );
            }
        }

        return $_aJobs;
    }

    /**
     * @param string $sJobFile
     * @param JobEntity $oJobEntity
     * @return bool
     */
    private function hasDumpFile($sJobFile, JobEntity $oJobEntity)
    {
        $this->oFileSystemService->dumpFile(
            $sJobFile,
            json_encode($oJobEntity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return (file_exists($sJobFile));
    }
}