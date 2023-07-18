<?php

namespace SFW\Cacher;

/**
 * Invalid argument exception handler.
 */
class InvalidArgumentException extends CacheException implements \Psr\SimpleCache\InvalidArgumentException {}
