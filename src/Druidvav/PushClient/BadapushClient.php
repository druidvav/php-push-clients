<?php
namespace Druidvav\PushClient;

use Druidvav\PushClient\Entity\Message;
use Druidvav\PushClient\Entity\Payload;
use Druidvav\PushClient\Exception\BadapushClientException;
use Druidvav\PushClient\Exception\ClientException;
use Druidvav\PushClient\Exception\InternalErrorException;
use Druidvav\PushClient\Exception\InvalidPayloadException;
use Druidvav\PushClient\Exception\InvalidSubscribeIdException;

class BadapushClient
{
    protected $apiUrl;
    protected $method = 'payload.send';
    protected $apiKey;

    public function __construct($apiKey, $apiUrl = null)
    {
        $this->apiKey = $apiKey;
        if (!$apiUrl) {
            $this->apiUrl = 'http://badapush.ru/api/v2/jsonrpc';
        } else {
            $this->apiUrl = $apiUrl;
        }
    }

    /**
     * @param Payload $payload
     * @return string
     * @throws BadapushClientException
     * @throws ClientException
     * @throws InternalErrorException
     * @throws InvalidPayloadException
     * @throws InvalidSubscribeIdException
     */
    public function sendPayload(Payload $payload)
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id' => 1,
            'method' => $this->method,
            'params' => [
                'device_id' => $payload->getDeviceId(),
                'payload' => $payload->getPayload(),
                'is_development' => $payload->isDevelopment(),
                'external_id' => $payload->getExternalId()
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
                return !empty($data['result']['response']) ? $data['result']['response'] : 'ok';
            } elseif ($data['result']['result'] == 'error') {
                switch ($data['result']['error_code']) {
                    default: throw new ClientException($data['result']['error_message']);
                    case ClientException::TYPE: throw new ClientException($data['result']['error_message']);
                    case InvalidSubscribeIdException::TYPE: throw new InvalidSubscribeIdException($data['result']['error_message']);
                    case InvalidPayloadException::TYPE: throw new InvalidPayloadException($data['result']['error_message']);
                    case InternalErrorException::TYPE: throw new InternalErrorException($data['result']['error_message']);
                }
            }
        } elseif (!empty($data['error'])) {
            throw new BadapushClientException($data['error']['code'] . ': ' . $data['error']['message']);
        } elseif ($httpcode == 502) {
            throw new InternalErrorException('Service is temporary shut down');
        } elseif ($httpcode == 504) {
            throw new InternalErrorException('Service is temporary down');
        } elseif ($errno == 28) {
            throw new InternalErrorException('TIMEOUT ' . $error);
        }
        throw new BadapushClientException($httpcode . '/' . $errno . ': ' . ($error ?: $response));
    }
    
    /** @noinspection PhpDocSignatureInspection */
    /**
     * @param int $fromId
     * @return array|Message[]
     * @throws BadapushClientException
     * @throws InternalErrorException
     */
    public function retrieveErrors($fromId = 0)
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id' => 1,
            'method' => 'payload.getMessages',
            'params' => [ 'from' => $fromId ]
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization-Token: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $data = json_decode($responseData, true);
        if (!empty($data['result']['result'])) {
            if ($data['result']['result'] == 'ok') {
                $response = [ ];
                foreach ($data['result']['list'] as $row) {
                    $response[] = new Message($row);
                }
                return $response;
            } elseif ($data['result']['result'] == 'error') {
                throw new BadapushClientException($data['result']['error_message']);
            }
        } elseif (!empty($data['error'])) {
            throw new BadapushClientException($data['error']['code'] . ': ' . $data['error']['message']);
        } elseif ($httpcode == 502) {
            throw new InternalErrorException('Service is temporary shut down');
        } elseif ($httpcode == 504) {
            throw new InternalErrorException('Service is temporary down');
        } elseif ($errno == 28) {
            throw new InternalErrorException('TIMEOUT ' . $error);
        }
        throw new BadapushClientException($httpcode . '/' . $errno . ': ' . ($error ?: $responseData));
    }

    /**
     * @param $externalId
     * @param string $reason
     * @throws BadapushClientException
     * @throws InternalErrorException
     */
    public function cancelCallsByExternalId($externalId, $reason = '')
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id' => 1,
            'method' => 'caller.cancel',
            'params' => [ 'external_id' => $externalId, 'reason' => $reason ]
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
                return;
            } elseif ($data['result']['result'] == 'error') {
                throw new BadapushClientException($data['result']['error_message']);
            }
        } elseif (!empty($data['error'])) {
            throw new BadapushClientException($data['error']['code'] . ': ' . $data['error']['message']);
        } elseif ($httpcode == 502) {
            throw new InternalErrorException('Service is temporary shut down');
        } elseif ($httpcode == 504) {
            throw new InternalErrorException('Service is temporary down');
        } elseif ($errno == 28) {
            throw new InternalErrorException('TIMEOUT ' . $error);
        }
        throw new BadapushClientException($httpcode . '/' . $errno . ': ' . ($error ?: $response));
    }
}
