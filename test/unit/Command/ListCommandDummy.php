<?php
/**
 * @package: chapi
 *
 * @author:  ppokatilo
 * @since:   2017-08-84
 */

namespace unit\Command;

use Chapi\Commands\ListCommand;

class ListCommandDummy extends ListCommand
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
