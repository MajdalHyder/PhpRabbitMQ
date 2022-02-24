<?php
require_once dirname(__DIR__, 3). '/vendor/autoload.php';
require_once dirname(__DIR__, 2). '/Database/User.php';

use Database\User;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('Planning', 'topic', false, true, false, false, false);

list($queue_name, ,) = $channel->queue_declare('', false, true, false, false);
$channel->queue_bind($queue_name, 'Planning', 'save');

$callback = function($msg)
{
    $corr_id = json_decode($msg->body, true)['id'];
    echo "CORR_ID: " . $corr_id . "\n" . "BODY : " . $msg->body . "\n" . "Routing_key : " . $msg->get('routing_key') . "\n";
    $con =   new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $chan = $con->channel();

    $msg->ack();
    try {
        $user = new User();
        $user->createUser($msg->body);
    } catch (Exception $e) {
        echo 'Exception ', $e->getMessage(), "\n";  
    } 

    $chan->exchange_declare('Planning', 'topic', false, true, false);
    $new_msg = new AMQPMessage($msg->body, array('correlation_id' => $corr_id));
    $chan->basic_publish($new_msg, 'Planning', $corr_id);
    $chan->close();
    $con->close();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while($channel->is_open()){
    $channel->wait();
}

$channel->close();
$connection->close();