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
$channel->queue_bind($queue_name, 'Planning', 'get_day_info');

$callback = function($msg)
{
    $myContribution = "nan";
    $creneaux = new CreneauModel();
    $creneauxArray = $creneaux->getCreneaux();
    if (!$creneauxArray) {
        $myContribution = "";
    };
    $incomingMessage = json_decode($msg->body, true);
    $incomingMessage['Get_Info']= $myContribution;
    echo json_encode($incomingMessage);
    $con =   new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $chan = $con->channel();
    $msg->ack();
    $chan->exchange_declare('Planning', 'topic', false, true, false);
    $new_msg = new AMQPMessage(json_encode($incomingMessage));
    $chan->basic_publish($new_msg, 'Planning', 'check');
    $chan->close();
    $con->close();
    
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while($channel->is_open()){
    $channel->wait();
}

$channel->close();
$connection->close();