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
$channel->queue_bind($queue_name, 'Planning', 'save_slot');
$creneau = new CreneauModel();

$callback = function($msg) use ($creneau)
{
    $myContribution = "";
    $corr_id = json_decode($msg->body)->id;
    $incomingMessage = json_decode($msg->body);
    //var_dump($incomingMessage);
    // TODO Save to DB;
    $user_picked_date = json_encode($incomingMessage->requested_slot);
    try{
        $creneau->createCreneau($user_picked_date);
        $incomingMessage->Saved = true; //Bool

        $con =   new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $chan = $con->channel();
        $msg->ack();
        $chan->exchange_declare('Planning', 'topic', false, true, false);
        var_dump(json_encode($incomingMessage));
        $new_msg = new AMQPMessage(json_encode($incomingMessage));
        $chan->basic_publish($new_msg, 'Planning', $corr_id);
        var_dump($corr_id);
        $chan->close();
        $con->close(); 
    } catch(Exception $e) {
        echo $e;
    }

};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while($channel->is_open()){
    $channel->wait();
}

$channel->close();
$connection->close();