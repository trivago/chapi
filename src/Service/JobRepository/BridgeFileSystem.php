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
     * @var array
     */
    private $aGroupedApps = [];

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
     * @return JobEntityInterface[]
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
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     * @throws JobLoadException
     */
    public function addJob(JobEntityInterface $oJobEntity)
    {
        // generate job file path by name
        $_sJobFile = $this->generateJobFilePath($oJobEntity);

        if ($this->hasDumpFile($_sJobFile, $oJobEntity))
        {
            $this->setJobFileToMap($oJobEntity->getKey(), $_sJobFile);
            return true;
        }

        return false;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        if ($oJobEntity->getEntityType() == JobEntityInterface::CHRONOS_TYPE)
        {
            return $this->hasDumpFile(
                $this->getJobFileFromMap($oJobEntity->getKey()),
                $oJobEntity
            );
        }

        if (!in_array($oJobEntity->getKey(), $this->aGroupedApps))
        {
            return $this->hasDumpFile(
                $this->getJobFileFromMap($oJobEntity->getKey()),
                $oJobEntity
            );
        }

        // marathon's group case where app belongs to a group file
        return $this->dumpFileWithGroup(
            $this->getJobFileFromMap($oJobEntity->getKey()),
            $oJobEntity
        );
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity)
    {
        $_sJobFile = $this->getJobFileFromMap($oJobEntity->getKey());
        $this->oFileSystemService->remove($_sJobFile);

        return $this->hasUnsetJobFileFromMap($oJobEntity->getKey(), $_sJobFile);
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return string
     */
    private function generateJobFilePath(JobEntityInterface $oJobEntity)
    {
        if ($oJobEntity->getEntityType() == JobEntityInterface::CHRONOS_TYPE)
        {
            $_sJobPath = str_replace(
                $this->aDirectorySeparators,
                DIRECTORY_SEPARATOR,
                $oJobEntity->getKey()
            );
        }
        else
        {
            $_sJobPath = $oJobEntity->getKey();
        }

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
     * @return JobEntityInterface[]
     * @throws JobLoadException
     */
    private function loadJobsFromFileContent(array $aJobFiles, $bSetToFileMap)
    {
        $_aJobs = [];

        foreach ($aJobFiles as $_sJobFilePath)
        {
            $_aJobEntities = [];
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
                if (property_exists($_aTemp, "name")) // chronos
                {
                    $_aJobEntities[] = new ChronosJobEntity($_aTemp);

                } else if (property_exists($_aTemp, "id")) //marathon
                {
                    if (property_exists($_aTemp, "apps"))
                    {
                        // store individual apps like single apps
                        foreach ($_aTemp->apps as $_oApp)
                        {
                            $_oGroupEntity = new MarathonAppEntity($_oApp);
                            $this->aGroupedApps[] = $_oApp->id;
                            $_aJobEntities[] = $_oGroupEntity;
                        }
                    }
                    else
                    {
                        $_aJobEntities[] = new MarathonAppEntity($_aTemp);
                    }

                } else {
                    throw new JobLoadException(
                        "Could not distinguish job as either chronos or marathon",
                        JobLoadException::ERROR_CODE_UNKNOWN_ENTITY_TYPE
                    );
                }

                /** @var JobEntityInterface $_oJobEntity */
                foreach ($_aJobEntities as $_oJobEntity)
                {
                    if ($bSetToFileMap)
                    {
                        // set path to job file map
                        $this->setJobFileToMap($_oJobEntity->getKey(), $_sJobFilePath);
                    }

                    $_aJobs[] = $_oJobEntity;
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
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    private function hasDumpFile($sJobFile, JobEntityInterface $oJobEntity)
    {
        $this->oFileSystemService->dumpFile(
            $sJobFile,
            json_encode($oJobEntity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return (file_exists($sJobFile));
    }


    private function dumpFileWithGroup($sJobFile, JobEntityInterface $oJobEntity)
    {
        $_sGroupConfig = file_get_contents($sJobFile);

        $_oDecodedConfig = json_decode(preg_replace(
            '~\/\*(.*?)\*\/~mis',
            '',
            $_sGroupConfig
        ));

        if (!property_exists($_oDecodedConfig, "apps"))
        {
            throw new \RuntimeException(sprintf(
                'Job file %s does not contain group configuration. But, "%s" belogs to group %s',
                $sJobFile,
                $oJobEntity->getKey(),
                $oJobEntity->group
            ));
        }

        $_bAppFound = false;
        foreach ($_oDecodedConfig->apps as $key => $_oApp)
        {
            if ($_oApp->id == $oJobEntity->getKey())
            {
                $_oDecodedConfig->apps[$key] = $oJobEntity;
                $_bAppFound = true;
            }
        }

        if (!$_bAppFound)
        {
            throw new \RuntimeException(sprintf(
                'Could update job. job %s could not be found in the group file %s.',
                $oJobEntity->getKey(),
                $sJobFile
            ));
        }

        $_sUpdatedConfig = json_encode($_oDecodedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->oFileSystemService->dumpFile(
            $sJobFile,
            $_sUpdatedConfig
        );

        return (file_exists($sJobFile));
    }
}