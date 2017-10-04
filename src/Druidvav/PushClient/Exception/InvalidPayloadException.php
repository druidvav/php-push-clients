<?php
namespace Druidvav\PushClient\Exception;

class InvalidPayloadException extends ClientException
{
    const TYPE = 'invalid_payload';

    protected $type = self::TYPE;
}