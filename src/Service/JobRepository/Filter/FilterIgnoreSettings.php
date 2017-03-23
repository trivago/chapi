<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-01
 *
 */

namespace Chapi\Service\JobRepository\Filter;


use Chapi\Component\Config\ChapiConfigInterface;
use Chapi\Entity\JobEntityInterface;
use Psr\Log\LoggerInterface;

class FilterIgnoreSettings implements JobFilterInterface
{
    /**
     * @var array[]
     */
    private $aSearchPatterns;

    /**
     * @var LoggerInterface
     */
    private $oLogger;

    /**
     * @var ChapiConfigInterface
     */
    private $oConfig;

    /**
     * FilterIgnoreSettings constructor.
     * @param LoggerInterface $oLogger
     * @param ChapiConfigInterface $oConfig
     */
    public function __construct(
        LoggerInterface $oLogger,
        ChapiConfigInterface $oConfig
    )
    {
        $this->oLogger = $oLogger;
        $this->oConfig = $oConfig;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $oJobEntity)
    {
        $_aSearchPatterns = $this->getSearchPatterns();

        foreach ($_aSearchPatterns['ignore'] as $_sPatternIgnore)
        {
            if (fnmatch($_sPatternIgnore, $oJobEntity->getKey()))
            {
                $this->oLogger->debug(
                    sprintf('FilterIgnoreSettings :: IGNORE "%s" FOR "%s"', $_sPatternIgnore, $oJobEntity->getKey())
                );

                foreach ($_aSearchPatterns['ignore_not'] as $_sPatternIgnoreNot)
                {
                    if (fnmatch($_sPatternIgnoreNot, $oJobEntity->getKey())) {
                        $this->oLogger->debug(
                            sprintf('FilterIgnoreSettings ::   IGNORE NOT "%s" FOR "%s"', $_sPatternIgnoreNot, $oJobEntity->getKey())
                        );

                        return true;
                    }
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @return array<*,array>
     */
    private function getSearchPatterns()
    {
        if (!is_null($this->aSearchPatterns))
        {
            return $this->aSearchPatterns;
        }

        $_aProfileConfig = $this->oConfig->getProfileConfig();
        $_aSearchPatterns = [
            'ignore' => [],
            'ignore_not' => []
        ];

        if (isset($_aProfileConfig['ignore']))
        {

            foreach ($_aProfileConfig['ignore'] as $_sSearchPattern)
            {
                if ('!' == substr($_sSearchPattern, 0, 1))
                {
                    $_aSearchPatterns['ignore_not'][] = substr($_sSearchPattern, 1);
                }
                else
                {
                    $_aSearchPatterns['ignore'][] = $_sSearchPattern;
                }
            }
        }

        return $this->aSearchPatterns = $_aSearchPatterns;
    }
}