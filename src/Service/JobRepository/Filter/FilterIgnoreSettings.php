<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-01
 *
 */

namespace Chapi\Service\JobRepository\Filter;


use Chapi\Entity\JobEntityInterface;
use Psr\Log\LoggerInterface;

class FilterIgnoreSettings implements JobFilterInterface
{
    /**
     * @var string[]
     */
    private $aDirectoryPaths = [];

    /**
     * @var string[]
     */
    private $aSearchPatterns;

    /**
     * @var LoggerInterface
     */
    private $oLogger;

    /**
     * FilterIgnoreSettings constructor.
     * @param $aDirectoryPaths
     */
    public function __construct(
        $aDirectoryPaths,
        LoggerInterface $oLogger
    )
    {
        $this->aDirectoryPaths = $aDirectoryPaths;
        $this->oLogger = $oLogger;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $oJobEntity)
    {
        $_aSearchPatterns = $this->getSearchPatterns();
        foreach ($_aSearchPatterns as $_sSearchPattern)
        {
            $_sRegEx = sprintf('~%s~s', $_sSearchPattern);

            if (preg_match($_sRegEx, $oJobEntity->getKey()))
            {
                $this->oLogger->debug(
                    sprintf('FilterIgnoreSettings :: HIT "%s" FOR "%s"', $_sSearchPattern, $oJobEntity->getKey())
                );
                return false;
            }

        }

        return true;
    }

    /**
     * @return string[]
     */
    private function getSearchPatterns()
    {
        if (!is_null($this->aSearchPatterns))
        {
            return $this->aSearchPatterns;
        }

        $_aSearchPatterns = [];

        foreach ($this->aDirectoryPaths as $_sDirectoryPath)
        {
            if (!is_dir($_sDirectoryPath))
            {
                throw new \RuntimeException(sprintf('Path "%s" is not valid', $_sDirectoryPath));
            }

            $this->getSearchPatternsFromDir($_sDirectoryPath, $_aSearchPatterns);
        }

        return $this->aSearchPatterns = $_aSearchPatterns;
    }

    /**
     * @param string $sDirectoryPath
     * @param array $aSearchPatterns
     */
    private function getSearchPatternsFromDir($sDirectoryPath, &$aSearchPatterns=[])
    {
        $_sIgnoreFilePath = $sDirectoryPath . DIRECTORY_SEPARATOR . '.chapiignore';

        if (is_file($_sIgnoreFilePath))
        {
            $_oFile = new \SplFileObject($_sIgnoreFilePath);

            while (!$_oFile->eof())
            {
                $_sSearchPattern = trim($_oFile->fgets());
                if ($_sSearchPattern)
                {
                    $aSearchPatterns[] = $_sSearchPattern;
                }
            }

            // Unset the file to call __destruct(), closing the file handle.
            $_oFile = null;
        }
    }
}