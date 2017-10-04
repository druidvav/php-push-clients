<?php
namespace Druidvav\PushClient;

use Druidvav\PushClient\Entity\Message;
use Druidvav\PushClient\Exception\BadapushClientException;
use Druidvav\PushClient\Exception\InternalErrorException;

class BadapushQueueClient extends BadapushClient
{
    protected $method = 'enqueuePayload';

    public function retrieveErrors($fromId = 0)
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id' => 1,
            'method' => 'getQueueErrors',
            'params' => [ 'from' => $fromId ]
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
        throw new BadapushClientException($httpcode . '/' . $errno . ': ' . ($error ?: $response));
    }
}
