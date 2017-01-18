<?php
namespace Druidvav\PushClient\Exception;

class InvalidPayloadException extends ClientException
{
    protected $type = 'invalid_payload';
}