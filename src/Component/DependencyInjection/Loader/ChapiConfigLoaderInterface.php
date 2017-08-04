<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2017-03-23
 *
 */

namespace Chapi\Component\DependencyInjection\Loader;

interface ChapiConfigLoaderInterface
{
    /**
     * @return void
     */
    public function loadProfileParameters();
}
