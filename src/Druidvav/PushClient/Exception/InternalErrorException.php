<?php
namespace Druidvav\PushClient\Exception;

class InternalErrorException extends ClientException
{
    const TYPE = 'internal_error';

    protected $type = self::TYPE;
}