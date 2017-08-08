<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-25
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/36
 */


namespace Chapi\Entity\DatePeriod;

class Iso8601Entity
{
    const REG_EX_ISO_8601_STRING = '#(R[0-9]*)/(.*)/(P.*)#';

    /** @var string  */
    public $iso8601 = '';

    /** @var string  */
    public $repeat = '';

    /** @var string  */
    public $startTime = '';

    /** @var string  */
    public $interval = '';

    /**
     * @param string $iso8601
     */
    public function __construct($iso8601)
    {
        $this->iso8601 = $iso8601;

        $matches = $this->parseIsoString();
        if (count($matches) != 4) {
            throw new \InvalidArgumentException(sprintf("Can't parse '%s' as iso 8601 string.", $iso8601));
        }

        $this->repeat = $matches[1];
        $this->startTime = $matches[2];
        $this->interval = $matches[3];
    }

    /**
     * @return string[]
     */
    private function parseIsoString()
    {
        $matches = [];

        preg_match(self::REG_EX_ISO_8601_STRING, $this->iso8601, $matches);

        return $matches;
    }
}
