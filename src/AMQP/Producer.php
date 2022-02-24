<?php

namespace AMQP;

require dirname(__DIR__,2) . "/vendor/autoload.php";

use Dotenv\Dotenv;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\src\AMQP\baseAMQP;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Producer extends myAMQP
{
    public function publish_to_queue(string $messageBody)
    {
        $this->channelInit();
        $message = new AMQPMessage($messageBody, array(
            'content_type' => 'application/json', 
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ));
        $this->channel->basic_publish($message, "DEA");
    }
}