<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-03
 *
 */


namespace unit\Component\Cache;

use Chapi\Component\Cache\DoctrineCache;
use Prophecy\Argument;

class DoctrineCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $cache;

    /**
     * set up default mocks
     */
    public function setUp()
    {
        $this->cache = $this->prophesize('Doctrine\Common\Cache\Cache');
    }

    public function testSetSuccess()
    {
        $testKey = 'key.test';
        $testValue = 'value';
        $testTtl = 10;

        $testReturn = true;

        $this->cache->save(
            Argument::containingString($testKey),
            Argument::exact($testValue),
            Argument::exact($testTtl)
        )->shouldBeCalledTimes(1)->willReturn($testReturn);

        $doctrineCache = new DoctrineCache($this->cache->reveal(), 'cache_prefix');

        $this->assertTrue(
            $doctrineCache->set($testKey, $testValue, $testTtl)
        );
    }

    public function testSetFailure()
    {
        $testKey = 'key.test';
        $testValue = 'value';
        $testTtl = 10;

        $testReturn = false;

        $this->cache->save(
            Argument::containingString($testKey),
            Argument::exact($testValue),
            Argument::exact($testTtl)
        )->shouldBeCalledTimes(1)->willReturn($testReturn);

        $doctrineCache = new DoctrineCache($this->cache->reveal(), 'cache_prefix');

        $this->assertFalse(
            $doctrineCache->set($testKey, $testValue, $testTtl)
        );
    }

    public function testGetSuccess()
    {
        $testKey = 'key.test';
        $testValue = 'value';

        $this->cache->contains(
            Argument::containingString($testKey)
        )->shouldBeCalledTimes(1)->willReturn(true);

        $this->cache->fetch(
            Argument::containingString($testKey)
        )->shouldBeCalledTimes(1)->willReturn($testValue);

        $doctrineCache = new DoctrineCache($this->cache->reveal(), 'cache_prefix');

        $this->assertEquals(
            $testValue,
            $doctrineCache->get($testKey)
        );
    }

    public function testGetNotSetted()
    {
        $testKey = 'key.test';

        $this->cache->contains(
            Argument::containingString($testKey)
        )->shouldBeCalledTimes(1)->willReturn(false);

        $this->cache->fetch(
            Argument::containingString($testKey)
        )->shouldNotBeCalled();

        $doctrineCache = new DoctrineCache($this->cache->reveal(), 'cache_prefix');

        $this->assertNull(
            $doctrineCache->get($testKey)
        );
    }

    public function testDeleteSuccess()
    {
        $testKey = 'key.test';

        $this->cache->delete(
            Argument::containingString($testKey)
        )->shouldBeCalledTimes(1)->willReturn(true);

        $doctrineCache = new DoctrineCache($this->cache->reveal(), 'cache_prefix');

        $this->assertTrue(
            $doctrineCache->delete($testKey)
        );
    }

    public function testDeleteFailure()
    {
        $testKey = 'key.test';

        $this->cache->delete(
            Argument::containingString($testKey)
        )->shouldBeCalledTimes(1)->willReturn(false);

        $doctrineCache = new DoctrineCache($this->cache->reveal(), 'cache_prefix');

        $this->assertFalse(
            $doctrineCache->delete($testKey)
        );
    }
}
