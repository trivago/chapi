<?php
/**
 * @package: chapi
 *
 * @author:  ppokatilo
 * @since:   2017-08-84
 */

namespace unit\Command;

use Chapi\Commands\SchedulingViewCommand;

class SchedulingViewCommandDummy extends SchedulingViewCommand
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
