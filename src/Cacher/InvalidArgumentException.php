<?php

namespace SFW\Cacher;

class InvalidArgumentException extends \SFW\InvalidArgumentException implements
    Exception, \Psr\SimpleCache\InvalidArgumentException
{
}
