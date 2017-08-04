<?php
/**
 * @package: chapi
 *
 * @author:  ppokatilo
 * @since:   2017-08-84
 */

namespace unit\Command;

use Chapi\Commands\ConfigureCommand;

class ConfigureCommandDummy extends ConfigureCommand
{
    public static $oContainerDummy;

    public static $sHomeDirDummy;

    public static $oQustionHelperDummy;

    protected function getContainer()
    {
        return self::$oContainerDummy;
    }

    protected function isAppRunable()
    {
        return true;
    }

    protected function getHomeDir()
    {
        return self::$sHomeDirDummy;
    }

    public function getHelper($sHelper)
    {
        return ($sHelper == 'question') ? self::$oQustionHelperDummy : parent::getHelper($sHelper);
    }
}
