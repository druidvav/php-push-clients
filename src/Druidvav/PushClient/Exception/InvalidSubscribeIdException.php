<?php
namespace Druidvav\PushClient\Exception;

class InvalidSubscribeIdException extends ClientException
{
    const TYPE = 'invalid_id';

    protected $type = self::TYPE;
}