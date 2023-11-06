<?php
declare(strict_types=1);

namespace SFW\Cacher\Exception;

class Runtime extends \SFW\Exception\Runtime implements
    \SFW\Cacher\Exception,
    \Psr\SimpleCache\CacheException
{
}
