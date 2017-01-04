<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 03/01/17
 * Time: 15:41
 */

namespace Chapi\BusinessCase\JobManagement;


class StoreJobBusinessCaseFactory implements StoreJobBusinessCaseFactoryInterface
{
    /**
     * @var StoreJobBusinessCaseInterface[]
     */
    private $storeJobBusinessCases;

    public function addBusinesCase(StoreJobBusinessCaseInterface $oStoreJob)
    {
        $this->storeJobBusinessCases[] = $oStoreJob;

    }
    public function getBusinessCaseWithJob($sJobName) {
        /** @var StoreJobBusinessCaseInterface $oStore */
        foreach ($this->storeJobBusinessCases as $oStore)
        {
            if ($oStore->isJobAvailable($sJobName)) {
                return $oStore;
            }
        }
        return null;
    }

    /**
     * @return StoreJobBusinessCaseInterface[]
     */
    public function getAllStoreJobBusinessCase()
    {
        return $this->storeJobBusinessCases;
    }
}