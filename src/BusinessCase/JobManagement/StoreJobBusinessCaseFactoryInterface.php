<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-03
 *
 */

namespace Chapi\BusinessCase\JobManagement;

interface StoreJobBusinessCaseFactoryInterface
{
    const DIC_NAME = 'StoreJobBusinessCaseFactoryInterface';
    /**
     * @param StoreJobBusinessCaseInterface $storeJob
     * @return void
     */
    public function addBusinesCase(StoreJobBusinessCaseInterface $storeJob);

    /**
     * @return StoreJobBusinessCaseInterface[]
     */
    public function getAllStoreJobBusinessCase();

    /**
     * @param $jobName
     * @return StoreJobBusinessCaseInterface
     */
    public function getBusinessCaseWithJob($jobName);
}
