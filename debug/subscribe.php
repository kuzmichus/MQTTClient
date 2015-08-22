<?php
/**
 *
 * PHP version 5.5
 *
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */

require __DIR__ . '/../vendor/autoload.php';

$mqtt = new \MQTT\spMQTT('tcp://127.0.0.1:1883/');

\MQTT\spMQTTDebug::Enable();

$mqtt->setKeepalive(3600);
$connected = $mqtt->connect();
if (!$connected) {
    die("Not connected\n");
}


$topics['/#'] = 1;

$mqtt->subscribe($topics);

//$mqtt->unsubscribe(array_keys($topics));

$mqtt->loop('default_subscribe_callback');
/**
 * @param spMQTT $mqtt
 * @param string $topic
 * @param string $message
 */
function default_subscribe_callback($mqtt, $topic, $message) {
    printf("Message received: Topic=%s, Message=%s\n", $topic, $message);
}
