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
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobValidator\JobValidatorServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class BridgeFactory
{
    public static function getChronosBridge(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobValidatorServiceInterface $oJobEntityValidatorService,
        LoggerInterface $oLogger
    ) {
        if ($oApiClient->ping()) {
            return new BridgeChronos(
                $oApiClient,
                $oCache,
                $oJobEntityValidatorService,
                $oLogger);
        }

        return new DummyBridge();
    }

    public static function getMarathonBridge(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobValidatorServiceInterface $oJobEntityValidatorService,
        LoggerInterface $oLogger
    ) {
        if ($oApiClient->ping()) {
            return new BridgeMarathon(
                $oApiClient,
                $oCache,
                $oJobEntityValidatorService,
                $oLogger);
        }

        return new DummyBridge();
    }


    public static function getFilesystemBridge(
        Filesystem $oFileSystemService,
        CacheInterface $oCache,
        $sRepositoryDir
    ) {
        if (empty($sRepositoryDir)) {
            return new DummyBridge();
        }

        return new BridgeFileSystem(
            $oFileSystemService,
            $oCache,
            $sRepositoryDir
        );
    }

}