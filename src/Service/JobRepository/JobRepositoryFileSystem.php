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
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\JobEntity;
use Symfony\Component\Filesystem\Filesystem;

class JobRepositoryFileSystem implements JobRepositoryServiceInterface
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
     * @var JobCollection
     */
    private $oJobCollection;

    /**
     * @var array
     */
    private $aJobFiles = [];

    /**
     * @var array
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
     * @param string $sJobName
     * @return \Chapi\Entity\Chronos\JobEntity
     */
    public function getJob($sJobName)
    {
        $_aJobs = $this->getJobs();
        if (isset($_aJobs[$sJobName]))
        {
            return $_aJobs[$sJobName];
        }

        return new JobEntity();
    }

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs()
    {
        if (!is_null($this->oJobCollection))
        {
            return $this->oJobCollection;
        }

        $_aJobFiles = $this->getJobFiles();
        $_aJobs = [];

        foreach ($_aJobFiles as $_sJobFilePath)
        {
            // remove comment blocks
            $_aTemp = json_decode(
                preg_replace(
                    '~\/\*(.*?)\*\/~mis',
                    '',
                    file_get_contents($_sJobFilePath)
                )
            );

            $_oJobEntity = new JobEntity($_aTemp);
            $_aJobs[$_oJobEntity->name] = $_oJobEntity;

            // set path to job file map
            $this->aJobFileMap[$_oJobEntity->name] = $_sJobFilePath;
        }

        return $this->oJobCollection = new JobCollection($_aJobs);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return mixed
     */
    public function addJob(JobEntity $oJobEntity)
    {
        // generate job file path by name
        $_sJobFile = $this->generateJobFilePath($oJobEntity);

        $this->oFileSystemService->dumpFile(
            $_sJobFile,
            json_encode($oJobEntity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        if (file_exists($_sJobFile))
        {
            // set path to job file map
            $this->aJobFileMap[$oJobEntity->name] = $_sJobFile;

            return true;
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return mixed
     */
    public function updateJob(JobEntity $oJobEntity)
    {
        if (!isset($this->aJobFileMap[$oJobEntity->name]))
        {
            throw new \RuntimeException(sprintf('Can\'t find file for job "%s"', $oJobEntity->name));
        }

        // overwrite job file
        $_sJobFile = $this->aJobFileMap[$oJobEntity->name];

        $this->oFileSystemService->dumpFile(
            $_sJobFile,
            json_encode($oJobEntity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return (file_exists($_sJobFile));
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName)
    {
        if (!isset($this->aJobFileMap[$sJobName]))
        {
            throw new \RuntimeException(sprintf('Can\'t find file for job "%s"', $sJobName));
        }

        // overwrite job file
        $_sJobFile = $this->aJobFileMap[$sJobName];

        $this->oFileSystemService->remove($_sJobFile);

        if (!file_exists($_sJobFile))
        {
            // unset path from job file map
            unset($this->aJobFileMap[$sJobName]);
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getJobFiles()
    {
        if (!empty($this->aJobFiles))
        {
            return $this->aJobFiles;
        }

        return $this->aJobFiles = $this->getJobFilesFromFileSystem($this->sRepositoryDir);
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

        $_aTemp = glob(rtrim($sPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*');

        foreach ($_aTemp as $_sPath)
        {
            if (is_file($_sPath) && preg_match('~\.json~i', $_sPath))
            {
                $aJobFiles[] = $_sPath;
            }
            elseif (is_dir($_sPath))
            {
                $this->getJobFilesFromFileSystem($_sPath, $aJobFiles);
            }
        }

        return $aJobFiles;
    }
}