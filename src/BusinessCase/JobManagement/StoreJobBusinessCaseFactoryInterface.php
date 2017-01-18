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
    const DIC_NAME = "StoreJobBusinessCaseFactoryInterface";
    /**
     * @param StoreJobBusinessCaseInterface $oStoreJob
     * @return
     */
    public function addBusinesCase(StoreJobBusinessCaseInterface $oStoreJob);

    /**
     * @return StoreJobBusinessCaseInterface[]
     */
    public function getAllStoreJobBusinessCase();

    /**
     * @param $sJobName
     * @return StoreJobBusinessCaseInterface
     */
    public function getBusinessCaseWithJob($sJobName);
}