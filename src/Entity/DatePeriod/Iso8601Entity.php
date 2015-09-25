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
    public $sIso8601 = '';

    /** @var string  */
    public $sRepeat = '';

    /** @var string  */
    public $sStartTime = '';

    /** @var string  */
    public $sInterval = '';

    /**
     * @param string $sIso8601
     */
    public function __construct($sIso8601)
    {
        $this->sIso8601 = $sIso8601;

        $_aMatch = $this->parseIsoString();
        if (count($_aMatch) != 4)
        {
            throw new \InvalidArgumentException(sprintf("Can't parse '%s' as iso 8601 string.", $sIso8601));
        }

        $this->sRepeat = $_aMatch[1];
        $this->sStartTime = $_aMatch[2];
        $this->sInterval = $_aMatch[3];
    }

    /**
     * @return array
     */
    private function parseIsoString()
    {
        $_aMatch = [];

        preg_match(self::REG_EX_ISO_8601_STRING, $this->sIso8601, $_aMatch);

        return $_aMatch;
    }
}