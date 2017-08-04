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
     * @param string $key
     * @param mixed $value
     * @param int $ttl = 0
     * @return bool
     */
    public function set($key, $value, $ttl = 0);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key);
}
