<?php
/**
 * @package: chapi
 *
 * @author:  agrunwald
 * @since:   2016-03-07
 *
 */

namespace Chapi\Entity\Http;

class AuthEntity
{
    /**
     * @var string
     */
    public $username = '';

    /**
     * @var string
     */
    public $password = '';

    /**
     * @param string $sUsername
     * @param string $sPassword
     */
    public function __construct($sUsername, $sPassword)
    {
        $this->username = $sUsername;
        $this->password = $sPassword;
    }
}