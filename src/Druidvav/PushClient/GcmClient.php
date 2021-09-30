<?php
namespace Druidvav\PushClient;

use Druidvav\PushClient\Entity\Payload;
use Druidvav\PushClient\Exception\GcmClientException;
use Druidvav\PushClient\Exception\InternalErrorException;
use Druidvav\PushClient\Exception\InvalidPayloadException;
use Druidvav\PushClient\Exception\InvalidSubscribeIdException;

class GcmClient
{
    const GCM_API_URL = 'https://fcm.googleapis.com/fcm/send';
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param Payload $payload
     * @return string
     * @throws GcmClientException
     * @throws InternalErrorException
     * @throws InvalidPayloadException
     * @throws InvalidSubscribeIdException
     */
    public function sendPayload(Payload $payload): string
    {
        if ($payload->getDeviceId() == 'BLACKLISTED') {
            throw new InvalidSubscribeIdException('BLACKLISTED');
        }
        $payloadData = $payload->getPayload();
        if (array_key_exists('data', $payloadData) || array_key_exists('notification', $payloadData)) {
            $payloadData['to'] = $payload->getDeviceId();
            return $this->send($payloadData);
        } else {
            return $this->send([
                'to' => $payload->getDeviceId(),
                'data' => $payloadData
            ]);
        }
    }

    /**
     * @param $data
     * @return string
     * @throws GcmClientException
     * @throws InternalErrorException
     * @throws InvalidPayloadException
     * @throws InvalidSubscribeIdException
     */
    protected function send($data): string
    {
        $ch = curl_init(self::GCM_API_URL);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpcode == 200) {
            if (!empty($data) && $data['failure']) {
                $error = $data['results'][0]['error'];
                if (preg_match('/(InternalServerError)/i', $error)) {
                    throw new InternalErrorException($error);
                } else if (preg_match('/(NotRegistered|MismatchSenderId)/i', $error)) {
                    throw new InvalidSubscribeIdException($error);
                } else if (preg_match('/(MessageTooBig)/i', $error)) {
                    throw new InvalidPayloadException($error);
                } else {
                    throw new GcmClientException($error);
                }
            } elseif ($data['results'][0]['message_id']) {
                return $data['results'][0]['message_id'];
            } else {
                throw new GcmClientException($response);
            }
        } elseif ($httpcode == 401 || $httpcode == 500 || $httpcode == 502 || $httpcode == 504) {
            throw new InternalErrorException('HTTP ' . $httpcode);
        } elseif ($errno == 28) {
            throw new InternalErrorException('TIMEOUT ' . $error);
        } elseif (preg_match('/(Unknown SSL protocol error)/i', $error)) {
            throw new InternalErrorException($error);
        } else {
            throw new GcmClientException($httpcode . '/' . $errno . ': ' . ($error ?: $response));
        }
    }
}
