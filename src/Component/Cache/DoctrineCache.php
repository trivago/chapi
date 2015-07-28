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
    private $oDoctrineCache;

    /**
     * @param Cache $oDoctrineCache
     */
    public function __construct(
        Cache $oDoctrineCache
    )
    {
        $this->oDoctrineCache = $oDoctrineCache;
    }

    /**
     * @param string $sKey
     * @param mixed $mValue
     * @param int $iTTL
     * @return bool
     */
    public function set($sKey, $mValue, $iTTL = 0)
    {
        return $this->oDoctrineCache->save($sKey, $mValue, $iTTL);
    }

    /**
     * @param string $sKey
     * @return mixed|null
     */
    public function get($sKey)
    {
        return ($this->oDoctrineCache->contains($sKey))
            ? $this->oDoctrineCache->fetch($sKey)
            : null
        ;
    }

    /**
     * @param string $sKey
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->oDoctrineCache->delete($sKey);
    }
}