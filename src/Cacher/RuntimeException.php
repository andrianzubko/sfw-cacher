<?php

namespace SFW\Cacher;

class RuntimeException extends \SFW\RuntimeException implements
    Exception, \Psr\SimpleCache\CacheException {}
