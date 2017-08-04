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
    private $searchPatterns;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ChapiConfigInterface
     */
    private $config;

    /**
     * FilterIgnoreSettings constructor.
     * @param LoggerInterface $logger
     * @param ChapiConfigInterface $config
     */
    public function __construct(
        LoggerInterface $logger,
        ChapiConfigInterface $config
    ) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $jobEntity)
    {
        $searchPatterns = $this->getSearchPatterns();

        foreach ($searchPatterns['ignore'] as $patternIgnore) {
            if (fnmatch($patternIgnore, $jobEntity->getKey())) {
                $this->logger->debug(
                    sprintf('FilterIgnoreSettings :: IGNORE "%s" FOR "%s"', $patternIgnore, $jobEntity->getKey())
                );

                foreach ($searchPatterns['ignore_not'] as $patternIgnoreNot) {
                    if (fnmatch($patternIgnoreNot, $jobEntity->getKey())) {
                        $this->logger->debug(
                            sprintf('FilterIgnoreSettings ::   IGNORE NOT "%s" FOR "%s"', $patternIgnoreNot, $jobEntity->getKey())
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
        if (!is_null($this->searchPatterns)) {
            return $this->searchPatterns;
        }

        $profileConfig = $this->config->getProfileConfig();
        $searchPatterns = [
            'ignore' => [],
            'ignore_not' => []
        ];

        if (isset($profileConfig['ignore'])) {
            foreach ($profileConfig['ignore'] as $searchPattern) {
                if ('!' == substr($searchPattern, 0, 1)) {
                    $searchPatterns['ignore_not'][] = substr($searchPattern, 1);
                } else {
                    $searchPatterns['ignore'][] = $searchPattern;
                }
            }
        }

        return $this->searchPatterns = $searchPatterns;
    }
}
