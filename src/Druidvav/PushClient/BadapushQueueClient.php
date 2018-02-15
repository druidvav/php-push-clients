<?php
namespace Druidvav\PushClient;

class BadapushQueueClient extends BadapushClient
{
    protected $method = 'payload.enqueue';
}
