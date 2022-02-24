<?php
namespace AMQP;
require dirname(__DIR__,2) . "/vendor/autoload.php";
use Dotenv\Dotenv;
use PhpAmqpLib\Message\AMQPMessage;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Consumer extends myAMQP
{
    public function callback(AMQPMessage $msg){}
    public function consum_Msgs($msg)
    {
        $channel = $msg->getChannel();
        $connection = $msg->getChannel()->getConnection();
        $msg->ack();
        $channel->close();
        $connection->close();
        $this->channel->basic_consume($this->queueName, $this->queueName, false, false, false, false, [$this, 'callback']);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->channel->close();
        $this->connection->close();
    }
    
}