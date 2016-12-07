
<?php
	
require('vendor/autoload.php');
define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


$url = parse_url(getenv('CLOUDAMQP_URL'));
$conn = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
$ch = $conn->channel();
$queue = 'basic_get_queue';
$ch->queue_declare($queue, false, true, false, false);
echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg) {
  echo " [x] Received ", $msg->body, "\n";
  
// If you are not using Composer (recommended)
// require("path/to/sendgrid-php/sendgrid-php.php");

$from = new SendGrid\Email(null, "saliha@dosomething.com");
$subject = "Hello World from beautiful world DOsomething!";
$to = new SendGrid\Email(null, "dscodetest@mailinator.com");
$content = new SendGrid\Content("text/plain", $msg->body);
$mail = new SendGrid\Mail($from, $subject, $to, $content);

$apiKey = getenv('SENDGRID_API_KEY');
$sg = new \SendGrid($apiKey);

$response = $sg->client->mail()->send()->post($mail);
echo $response->statusCode();
echo $response->headers();
echo $response->body();
};

$ch->basic_consume($queue, '', false, true, false, false, $callback);

while(count($ch->callbacks)) {
    $ch->wait();
}

/* 

$callback = function($msg) {
    $retrived_msg = $ch->basic_get($queue);
    $mymsg=$retrived_msg->body;
    echo "<p bgcolor=\"red\"> Thank you ;)". $mymsg ."</p>";
    $ch->basic_ack($retrived_msg->delivery_info['delivery_tag']);
};



// loop over incoming messages
while(count($ch->callbacks)) {
    $cha->wait();
}
 */

$ch->close();
$conn->close();






 
 ?>