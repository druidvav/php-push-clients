<?php
namespace Druidvav\PushClient;

use Druidvav\PushClient\Entity\Payload;
use Druidvav\PushClient\Exception\ApnsClientException;
use Druidvav\PushClient\Exception\InternalErrorException;
use Druidvav\PushClient\Exception\InvalidSubscribeIdException;

class ApnsClient
{
    protected $bundleId = '';
    protected $pemFile = '';

    public function __construct($bundleId, $pemFile)
    {
        $this->bundleId = $bundleId;
        $this->pemFile = $pemFile;
    }

    public function send($registrationId, $data, $optional = [ ])
    {
        $payload = new Payload();
        $payload->setDeviceId($registrationId);
        $payload->setPayload($optional);
        $payload->setPayloadAps($data);
        return $this->sendPayload($payload);
    }

    public function sendPayload(Payload $payload)
    {
        $ch = curl_init($this->getApiUrl($payload->isDevelopment()) . $payload->getDeviceId());
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload->getPayload()));
        curl_setopt($ch, CURLOPT_HTTP_VERSION, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ "apns-topic: {$this->bundleId}" ]);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->pemFile);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($httpcode == 200) {
            return true;
        } elseif ($errno == 28) {
            throw new InternalErrorException('TIMEOUT ' . $error);
        } else {
            $data = json_decode($response, true);
            if (!empty($data) && $data['reason']) {
                if (preg_match('/(Unregistered|BadDeviceToken|DeviceTokenNotForTopic)/i', $data['reason'])) {
                    throw new InvalidSubscribeIdException($data['reason']);
                } else {
                    throw new ApnsClientException($data['reason']);
                }
            }
            throw new ApnsClientException($httpcode . '/' . $errno . ': ' . ($error ?: $response));
        }
    }

    protected function getApiUrl($isDevelopment)
    {
        $server = $isDevelopment ? 'api.development.push.apple.com' : 'api.push.apple.com';
        return "https://$server/3/device/";
    }
}
