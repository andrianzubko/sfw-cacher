<?php

namespace SFW\Cacher\Exception;

class InvalidArgument extends \SFW\Exception\InvalidArgument implements
    \SFW\Cacher\Exception,
    \Psr\SimpleCache\InvalidArgumentException
{
}
