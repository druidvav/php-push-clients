<?php
namespace Druidvav\PushClient\Exception;

class InvalidSubscribeIdException extends ClientException
{
    protected $type = 'invalid_id';
}