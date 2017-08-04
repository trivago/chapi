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

class DoctrineCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oCache;

    /**
     * set up default mocks
     */
    public function setUp()
    {
        $this->oCache = $this->prophesize('Doctrine\Common\Cache\Cache');
    }

    public function testSetSuccess()
    {
        $_sTestKey = 'key.test';
        $_mTestValue = 'value';
        $_iTestTtl = 10;

        $_bTestReturn = true;

        $this->oCache->save(
            Argument::containingString($_sTestKey),
            Argument::exact($_mTestValue),
            Argument::exact($_iTestTtl)
        )->shouldBeCalledTimes(1)->willReturn($_bTestReturn);

        $_oDoctrineCache = new DoctrineCache($this->oCache->reveal(), 'cache_prefix');

        $this->assertTrue(
            $_oDoctrineCache->set($_sTestKey, $_mTestValue, $_iTestTtl)
        );
    }

    public function testSetFailure()
    {
        $_sTestKey = 'key.test';
        $_mTestValue = 'value';
        $_iTestTtl = 10;

        $_bTestReturn = false;

        $this->oCache->save(
            Argument::containingString($_sTestKey),
            Argument::exact($_mTestValue),
            Argument::exact($_iTestTtl)
        )->shouldBeCalledTimes(1)->willReturn($_bTestReturn);

        $_oDoctrineCache = new DoctrineCache($this->oCache->reveal(), 'cache_prefix');

        $this->assertFalse(
            $_oDoctrineCache->set($_sTestKey, $_mTestValue, $_iTestTtl)
        );
    }

    public function testGetSuccess()
    {
        $_sTestKey = 'key.test';
        $_mTestValue = 'value';

        $this->oCache->contains(
            Argument::containingString($_sTestKey)
        )->shouldBeCalledTimes(1)->willReturn(true);

        $this->oCache->fetch(
            Argument::containingString($_sTestKey)
        )->shouldBeCalledTimes(1)->willReturn($_mTestValue);

        $_oDoctrineCache = new DoctrineCache($this->oCache->reveal(), 'cache_prefix');

        $this->assertEquals(
            $_mTestValue,
            $_oDoctrineCache->get($_sTestKey)
        );
    }

    public function testGetNotSetted()
    {
        $_sTestKey = 'key.test';

        $this->oCache->contains(
            Argument::containingString($_sTestKey)
        )->shouldBeCalledTimes(1)->willReturn(false);

        $this->oCache->fetch(
            Argument::containingString($_sTestKey)
        )->shouldNotBeCalled();

        $_oDoctrineCache = new DoctrineCache($this->oCache->reveal(), 'cache_prefix');

        $this->assertNull(
            $_oDoctrineCache->get($_sTestKey)
        );
    }

    public function testDeleteSuccess()
    {
        $_sTestKey = 'key.test';

        $this->oCache->delete(
            Argument::containingString($_sTestKey)
        )->shouldBeCalledTimes(1)->willReturn(true);

        $_oDoctrineCache = new DoctrineCache($this->oCache->reveal(), 'cache_prefix');

        $this->assertTrue(
            $_oDoctrineCache->delete($_sTestKey)
        );
    }

    public function testDeleteFailure()
    {
        $_sTestKey = 'key.test';

        $this->oCache->delete(
            Argument::containingString($_sTestKey)
        )->shouldBeCalledTimes(1)->willReturn(false);

        $_oDoctrineCache = new DoctrineCache($this->oCache->reveal(), 'cache_prefix');

        $this->assertFalse(
            $_oDoctrineCache->delete($_sTestKey)
        );
    }
}
