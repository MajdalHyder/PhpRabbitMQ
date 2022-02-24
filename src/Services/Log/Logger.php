<?php
require_once dirname(__DIR__, 3). '/vendor/autoload.php';

require_once dirname(__DIR__, 2). '/Database/User.php';

use Database\User;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('Planning', 'topic', false, true, false, false, false);

$channel->queue_declare('log_queue', false, true, false, false);
$channel->queue_bind('log_queue', 'Planning', '#');

$callback = function($msg)
{
    $corr_id = json_decode($msg->body, true)['id'];
    echo "CORR_ID: " . $corr_id . "\n" . "BODY : " . $msg->body . "\n" . "Routing_key : " . $msg->get('routing_key') . "\n";
    try {
        $user = new User();
        $user->createUser($msg->body);
    } catch (Exception $e) {
        echo 'Exception ', $e->getMessage(), "\n";  
    } 
};

$channel->basic_consume('log_queue', '', false, false, false, false, $callback);

while($channel->is_open()){
    $channel->wait();
}

$channel->close();
$connection->close();