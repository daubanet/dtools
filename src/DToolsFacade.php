<?php

namespace Daubanet\DTools;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Daubanet\DTools\Skeleton\SkeletonClass
 */
class DToolsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dtools';
    }
}
