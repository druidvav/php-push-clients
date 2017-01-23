<?php
namespace Druidvav\PushClient;

use Druidvav\PushClient\Entity\Payload;
use Druidvav\PushClient\Exception\ClientException;
use Druidvav\PushClient\Exception\InternalErrorException;
use Druidvav\PushClient\Exception\InvalidPayloadException;
use Druidvav\PushClient\Exception\InvalidSubscribeIdException;

class BadapushClient
{
    protected $apiUrl = 'http://badapush.ru/api/v1/jsonrpc';
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function sendPayload(Payload $payload)
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id' => 1,
            'method' => 'sendPayload',
            'params' => [
                'device_id' => $payload->getDeviceId(),
                'payload' => $payload->getPayload(),
                'is_development' => $payload->isDevelopment()
            ]
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization-Token: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!empty($data['result']['result'])) {
            if ($data['result']['result'] == 'ok') {
                return $data['result']['response'];
            } elseif ($data['result']['result'] == 'error') {
                switch ($data['result']['error_code']) {
                    default: throw new ClientException($data['result']['error_message']);
                    case 'error': throw new ClientException($data['result']['error_message']);
                    case 'invalid_id': throw new InvalidSubscribeIdException($data['result']['error_message']);
                    case 'invalid_payload': throw new InvalidPayloadException($data['result']['error_message']);
                    case 'internal_error': throw new InternalErrorException($data['result']['error_message']);
                }
            }
        } elseif (!empty($data['error'])) {
            throw new ClientException($data['error']['code'] . ': ' . $data['error']['message']);
        } elseif ($httpcode == 502) {
            throw new InternalErrorException('Service is temporary shut down');
        }
        throw new ClientException($httpcode . '/' . $errno . ': ' . ($error ?: $response));
    }
}
