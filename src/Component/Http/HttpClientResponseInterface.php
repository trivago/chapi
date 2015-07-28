<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Http;

interface HttpClientResponseInterface
{
    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return array
     */
    public function json();
}