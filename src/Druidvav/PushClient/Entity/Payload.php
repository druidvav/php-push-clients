<?php
namespace Druidvav\PushClient\Entity;

use Druidvav\PushClient\Exception\GcmClientException;
use Druidvav\PushClient\Exception\InternalErrorException;
use Druidvav\PushClient\Exception\InvalidSubscribeIdException;

class Payload
{
    protected $deviceId;
    protected $payload;
    protected $isDevelopment;

    public function __construct($deviceId = '', $payload = [ ], $isDevelopment = false)
    {
        $this->deviceId = $deviceId;
        $this->payload = $payload;
        $this->isDevelopment = $isDevelopment;
    }

    /**
     * @return bool
     */
    public function isDevelopment()
    {
        return $this->isDevelopment;
    }

    /**
     * @param bool $isDevelopment
     */
    public function setIsDevelopment($isDevelopment)
    {
        $this->isDevelopment = $isDevelopment;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $data
     */
    public function setPayload($data)
    {
        $this->payload = $data;
    }


    /**
     * @param array $data
     */
    public function setPayloadAps($data)
    {
        $this->payload['aps'] = $data;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @param string $deviceId
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
    }
}
