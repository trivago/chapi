<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-22
 *
 */

namespace Chapi\Component\Config;


interface ChapiConfigInterface
{
    const CONFIG_FILE_NAME = '.chapiconfig';

    /**
     * @return array
     */
    public function getProfileConfig();

    /**
     * @return array
     */
    public function getConfig();
}