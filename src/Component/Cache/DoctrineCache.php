<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Cache;

use Doctrine\Common\Cache\Cache;

class DoctrineCache implements CacheInterface
{
    /**
     * @var Cache
     */
    private $doctrineCache;

    /**
     * @var string
     */
    private $cachePrefix = '';

    /**
     * @param Cache $doctrineCache
     */
    public function __construct(
        Cache $doctrineCache,
        $cachePrefix
    ) {
        $this->doctrineCache = $doctrineCache;
        $this->cachePrefix = substr(
            md5($cachePrefix),
            0,
            6
        ) . '.';
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = 0)
    {
        return $this->doctrineCache->save($this->cachePrefix . $key, $value, $ttl);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return ($this->doctrineCache->contains($this->cachePrefix . $key))
            ? $this->doctrineCache->fetch($this->cachePrefix . $key)
            : null
        ;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->doctrineCache->delete($this->cachePrefix . $key);
    }
}
