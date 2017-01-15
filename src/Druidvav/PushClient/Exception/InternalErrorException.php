<?php
namespace Druidvav\PushClient\Exception;

class InternalErrorException extends ClientException
{
    protected $type = 'internal_error';
}