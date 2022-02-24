<?php
namespace AMQP;
require dirname(__DIR__,2) . "/vendor/autoload.php";

use Dotenv\Dotenv;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
class myAMQP
{
    
    private $connection;
    private $channel;
    private $queueName;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection($_ENV['RABBIT_HOST'], 
                                                     $_ENV['RABBIT_PORT'], 
                                                     $_ENV['RABBIT_USER'], 
                                                     $_ENV['RABBIT_PASS'],
                                                     '/',false, 'AMQPLAIN', null, 'fr_FR', 3.0, 120.0, null, true, 60.0);

        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare($_ENV['RABBIT_EXCHANGE'], 'topic', false, true, false);
    }


    public function channelInit() {
        $this->channel = $this->connection->channel();
    }

    public function closeConnection()
    {
        $this->connection->close();
    }

    public function publish_to_queue(string $messageBody)
    {
        $message = new AMQPMessage($messageBody, array(
            'content_type' => 'application/json', 
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ));
        $this->channel->basic_publish($message, "DEA");
    }

    public function listenForMsgs()
    {
        $this->channel->basic_consume($this->queueName, $this->queueName, false, false, false, false, [$this, 'callback']);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->channel->close();
        $this->connection->close();
    }

    public function callback(AMQPMessage $msg){}
}