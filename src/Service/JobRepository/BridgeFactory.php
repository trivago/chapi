<?php
/**
 * @package: chapi
 *
 * @author:  bthapaliya
 * @since:   2017-02-14
 */


namespace Chapi\Service\JobRepository;

use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Service\JobValidator\JobValidatorServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class BridgeFactory
{
    public static function getChronosBridge(
        ApiClientInterface $apiClient,
        CacheInterface $cache,
        JobValidatorServiceInterface $jobEntityValidatorService,
        LoggerInterface $logger
    ) {
        if ($apiClient->ping()) {
            return new BridgeChronos(
                $apiClient,
                $cache,
                $jobEntityValidatorService,
                $logger
            );
        }

        return new DummyBridge($logger);
    }

    public static function getMarathonBridge(
        ApiClientInterface $apiClient,
        CacheInterface $cache,
        JobValidatorServiceInterface $jobEntityValidatorService,
        LoggerInterface $logger
    ) {
        if ($apiClient->ping()) {
            return new BridgeMarathon(
                $apiClient,
                $cache,
                $jobEntityValidatorService,
                $logger
            );
        }

        return new DummyBridge($logger);
    }


    public static function getFilesystemBridge(
        Filesystem $fileSystemService,
        CacheInterface $cache,
        $repositoryDir,
        LoggerInterface $logger
    ) {
        if (empty($repositoryDir)) {
            return new DummyBridge($logger);
        }

        return new BridgeFileSystem(
            $fileSystemService,
            $cache,
            $repositoryDir
        );
    }
}
