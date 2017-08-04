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

    public function addBusinesCase(StoreJobBusinessCaseInterface $storeJob)
    {
        $this->storeJobBusinessCases[] = $storeJob;
    }
    public function getBusinessCaseWithJob($jobName)
    {
        /** @var StoreJobBusinessCaseInterface $store */
        foreach ($this->storeJobBusinessCases as $store) {
            if ($store->isJobAvailable($jobName)) {
                return $store;
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
