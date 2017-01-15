<?php
namespace Druidvav\PushClient\Exception;

class ClientException extends \Exception
{
    protected $type = 'error';

    public function getErrorType()
    {
        return $this->type;
    }
}