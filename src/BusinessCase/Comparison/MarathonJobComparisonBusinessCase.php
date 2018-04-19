<?php
/**
 *
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2016-12-14
 *
 */

namespace Chapi\BusinessCase\Comparison;

use Chapi\Component\Comparison\DiffCompareInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\AppEntity\DockerPortMapping;
use Chapi\Entity\Marathon\AppEntity\Fetch;
use Chapi\Entity\Marathon\AppEntity\Network;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;

class MarathonJobComparisonBusinessCase extends AbstractJobComparisionBusinessCase
{
    /**
     * @param JobRepositoryInterface $localRepository
     * @param JobRepositoryInterface $remoteRepository
     * @param DiffCompareInterface $diffCompare
     */
    public function __construct(
        JobRepositoryInterface $localRepository,
        JobRepositoryInterface $remoteRepository,
        DiffCompareInterface $diffCompare
    ) {
        $this->remoteRepository = $remoteRepository;
        $this->localRepository = $localRepository;
        $this->diffCompare = $diffCompare;
    }

    protected function preCompareModifications(JobEntityInterface &$localJob, JobEntityInterface &$remoteJob)
    {
        if (!$localJob instanceof MarathonAppEntity ||
            !$remoteJob instanceof MarathonAppEntity
        ) {
            throw new \RuntimeException('Required MarathonAppEntity. Something else encountered.');
        }
        // marathon returns portDefinitions values for auto configured port as well
        // we want to only check if the port is defined in local file.
        // otherwise we ignore the remote values.
        if (!$localJob->portDefinitions) {
            $remoteJob->portDefinitions = null;
        }

        if ($localJob->container && $localJob->container->docker &&
            $remoteJob->container && $remoteJob->container->docker) {
        // convert uris to fetchers
        foreach ($localJob->uris as $uri) {
            $localJob->fetch[] = new Fetch(["uri" => $uri, "extract" => true]);
        }
        $localJob->uris = [];
        foreach ($remoteJob->uris as $uri) {
            $remoteJob->fetch[] = new Fetch(["uri" => $uri, "extract" => true]);
        }
        $remoteJob->uris = [];

        if ($localJob->container && $remoteJob->container) {

            $localPortMappings = $localJob->container->portMappings;
            $remotePortMappings = $remoteJob->container->portMappings;

            usort($localPortMappings, DockerPortMapping::class . '::less');
            usort($remotePortMappings, DockerPortMapping::class . '::less');

            foreach ($localPortMappings as $index => $localPortMapping) {
                if ($localPortMapping->servicePort !== 0) {
                    continue;
                }

                if (!isset($remotePortMappings[$index])) {
                    continue;
                }

                $remotePortMapping = $remotePortMappings[$index];

                if (DockerPortMapping::less($remotePortMapping, $localPortMapping) != 0) {
                    $fixedPortMapping = clone $remotePortMapping;
                    $fixedPortMapping->servicePort = 0;

                    if (DockerPortMapping::less($fixedPortMapping, $localPortMapping) == 0) {
                        unset($localPortMappings[$index]);
                        unset($remotePortMappings[$index]);
                    }
                }
            }

            $localJob->container->portMappings = array_values($localPortMappings);
            $remoteJob->container->portMappings = array_values($remotePortMappings);
        }

        // set network to "host" when not containerized
        if (!$localJob->container) {
            $localJob->networks = [new Network(["mode" => "host"])];
        }
        if (!$remoteJob->container) {
            $remoteJob->networks = [new Network(["mode" => "host"])];
        }
    }

    /**
     * @return JobEntityInterface
     */
    protected function getEntitySetWithDefaults()
    {
        return new MarathonAppEntity();
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $jobEntityA
     * @param JobEntityInterface|ChronosJobEntity $jobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        // for now we don't have a concrete seperation
        // of types for marathon.
        return true;
    }

    /**
     * @param $property
     * @param $jobEntityA
     * @param $jobEntityB
     * @return bool
     */
    protected function isEntityEqual($property, JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        if (!$jobEntityA instanceof MarathonAppEntity ||
            !$jobEntityB instanceof MarathonAppEntity
        ) {
            throw new \RuntimeException('Required MarathonAppEntity. Something else encountered.');
        }

        return $this->isEqual($jobEntityA->{$property}, $jobEntityB->{$property});
    }

    /**
     * @param mixed $valueA
     * @param mixed $valueB
     * @return bool
     */
    private function isEqual($valueA, $valueB)
    {
        if (is_array($valueA) && is_array($valueB)) {
            return $this->isArrayEqual($valueA, $valueB);
        } elseif (is_object($valueA) && is_object($valueB)) {
            return $this->isArrayEqual(get_object_vars($valueA), get_object_vars($valueB));
        } elseif ((is_scalar($valueA) && is_scalar($valueB)) || (is_null($valueA) && is_null($valueB))) {
            return $valueA == $valueB;
        }

        return false;
    }

    /**
     * @param array $valuesA
     * @param array $valuesB
     * @return bool
     */
    private function isArrayEqual(array $valuesA, array $valuesB)
    {
        return $this->isArrayHalfEqual($valuesA, $valuesB) && $this->isArrayHalfEqual($valuesB, $valuesA);
    }

    /**
     * @param array $valuesA
     * @param array $valuesB
     * @return bool
     */
    private function isArrayHalfEqual(array $valuesA, array $valuesB)
    {
        foreach ($valuesA as $keyA => $valueA) {
            if (is_string($keyA)) {
                if (!array_key_exists($keyA, $valuesB) || !$this->isEqual($valueA, $valuesB[$keyA])) {
                    return false;
                }
            } else {
                foreach ($valuesB as $valueB) {
                    if ($this->isEqual($valueA, $valueB)) {
                        continue 2;
                    }
                }

                return false;
            }
        }

        return true;
    }
}
