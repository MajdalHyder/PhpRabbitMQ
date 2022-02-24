<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$corr_id = uniqid();
$payload = [];

$channel->exchange_declare('Planning', 'topic', false, true, false, false, false);

list($queue_name, , ) = $channel->queue_declare('', false, true, false, false);

$channel->queue_bind($queue_name, 'Planning', $corr_id);

$payload += ["id" => $corr_id];
$payload += ["requested_slot" => json_decode($_GET['data'], true)];

$con =   new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$chan = $con->channel();
$chan->exchange_declare('Planning', 'topic', false, true, false);
$new_msg = new AMQPMessage(json_encode($payload), array('correlation_id' => $corr_id));
$chan->basic_publish($new_msg, 'Planning', 'get_day_info');


$callback = function($msg)
{
    $channel = $msg->getChannel();
    $connection = $msg->getChannel()->getConnection();
    echo $msg->body;
    $msg->ack();
    $channel->close();
    $connection->close();
};


    $channel->basic_consume($queue_name , '', false, false, false, false, $callback);

    while($channel->is_open()){
        
            $channel->wait(null, false, 10);
        
    }