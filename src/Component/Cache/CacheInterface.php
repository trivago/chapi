<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Cache;

interface CacheInterface
{
    const DIC_NAME = 'CacheInterface';

    /**
     * @param string $sKey
     * @param mixed $mValue
     * @param int $iTTL = 0
     * @return bool
     */
    public function set($sKey, $mValue, $iTTL = 0);

    /**
     * @param string $sKey
     * @return mixed
     */
    public function get($sKey);

    /**
     * @param string $sKey
     * @return bool
     */
    public function delete($sKey);
}
