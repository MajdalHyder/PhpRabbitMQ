<?php
require_once dirname(__DIR__, 3). '/vendor/autoload.php';
require_once dirname(__DIR__, 2). '/Database/Creneau.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Database\CreneauModel;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('Planning', 'topic', false, true, false, false, false);

list($queue_name, ,) = $channel->queue_declare('', false, true, false, false);
$channel->queue_bind($queue_name, 'Planning', 'check');

$callback = function($msg)
{
    $myContribution = "";
    // TODO Compare Pulled dates with user selected range;
    $incomingMessage = json_decode($msg->body, true);
    $requested_time_range = json_decode($msg->body)->requested_slot; //$requested_time_range->start
    $retreived_time_range = json_decode($msg->body)->Get_Info;
    if(json_encode(strtotime($retreived_time_range))){
        $myContribution = true; // As in there are no corresponding dates and so the range is savable in db
    } else {
        $myContribution = false; // Conflict and we can't save; 
    };
    $incomingMessage['Available'] = $myContribution;
    $con =   new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $chan = $con->channel();
    $msg->ack();
    $chan->exchange_declare('Planning', 'topic', false, true, false);
    $new_msg = new AMQPMessage(json_encode($incomingMessage));
    $chan->basic_publish($new_msg, 'Planning', 'save_slot');
    $chan->close();
    $con->close();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while($channel->is_open()){
    $channel->wait();
}

$channel->close();
$connection->close();