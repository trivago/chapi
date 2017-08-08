<?php
/**
 * @package: chapi
 *
 * @author:  ppokatilo
 * @since:   2017-08-84
 */

namespace unit\Command;

use Chapi\Commands\InfoCommand;

class InfoCommandDummy extends InfoCommand
{
    public static $containerDummy;

    protected function getContainer()
    {
        return self::$containerDummy;
    }

    protected function isAppRunable()
    {
        return true;
    }
}
