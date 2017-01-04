<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 03/01/17
 * Time: 15:52
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