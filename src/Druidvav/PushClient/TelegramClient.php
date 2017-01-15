<?php
namespace Druidvav\PushClient;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Druidvav\PushClient\Exception\InternalErrorException;
use Druidvav\PushClient\Exception\InvalidSubscribeIdException;

class TelegramClient
{
    protected $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function sendMessage($data)
    {
        try {
            return $this->api->sendMessage($data);
        } catch (TelegramSDKException $e) {
            if (preg_match('/(chat not found|Bot was blocked by the user|user is deleted|user is deactivated|USER_DEACTIVATED)/i', $e->getMessage())) {
                throw new InvalidSubscribeIdException($e->getMessage());
            } elseif (preg_match('/(timed out after)/i', $e->getMessage())) {
                throw new InternalErrorException($e->getMessage());
            } else {
                throw $e;
            }
        }
    }
}
