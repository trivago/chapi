<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-03
 *
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
    public function getBusinessCaseWithJob($sJobName)
    {
        /** @var StoreJobBusinessCaseInterface $oStore */
        foreach ($this->storeJobBusinessCases as $oStore)
        {
            if ($oStore->isJobAvailable($sJobName))
            {
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