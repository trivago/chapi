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
    public static $oContainerDummy;

    protected function getContainer()
    {
        return self::$oContainerDummy;
    }

    protected function isAppRunable()
    {
        return true;
    }
}
