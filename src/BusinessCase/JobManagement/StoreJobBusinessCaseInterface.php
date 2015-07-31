<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\BusinessCase\JobManagement;


interface StoreJobBusinessCaseInterface
{
    const DIC_NAME = 'StoreJobBusinessCaseInterface';

    /**
     *
     */
    public function storeIndexedJobs();
}