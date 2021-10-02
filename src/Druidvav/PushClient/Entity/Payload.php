<?php
namespace Druidvav\PushClient\Entity;

use Exception;

class Payload
{
    protected string $deviceId;
    protected $payload;
    protected bool $isDevelopment;
    protected ?string $externalId;

    public function __construct(string $deviceId = '', array $payload = [ ], bool $isDevelopment = false)
    {
        $this->deviceId = $deviceId;
        $this->payload = $payload;
        $this->isDevelopment = $isDevelopment;
    }

    public function isDevelopment(): bool
    {
        return $this->isDevelopment;
    }

    public function setIsDevelopment(bool $isDevelopment): void
    {
        $this->isDevelopment = $isDevelopment;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array|string $data
     * @throws Exception
     */
    public function setPayload($data): void
    {
        if (!is_array($data) && !is_string($data)) {
            throw new Exception('Invalid payload type');
        }
        $this->payload = $data;
    }

    public function setPayloadAps(array $data): void
    {
        $this->payload['aps'] = $data;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }
}
