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
    public static $containerDummy;

    public static $homeDirDummy;

    public static $questionHelperDummy;

    protected function getContainer()
    {
        return self::$containerDummy;
    }

    protected function isAppRunable()
    {
        return true;
    }

    protected function getHomeDir()
    {
        return self::$homeDirDummy;
    }

    public function getHelper($helper)
    {
        return ($helper == 'question') ? self::$questionHelperDummy : parent::getHelper($helper);
    }
}
