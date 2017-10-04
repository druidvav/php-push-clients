<?php
namespace Druidvav\PushClient\Exception;

class ClientException extends \Exception
{
    const TYPE = 'error';

    protected $type = self::TYPE;

    public function getErrorType()
    {
        return $this->type;
    }
}