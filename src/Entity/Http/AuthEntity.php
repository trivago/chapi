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
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}
