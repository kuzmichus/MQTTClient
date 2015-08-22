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
//$mqtt->setAuth('sskaje', '123123');
$connected = $mqtt->connect();
if (!$connected) {
    die("Not connected\n");
}
$mqtt->ping();

//$msg = str_repeat('1234567890', 209716);
$msg = str_repeat('1234567890', 2);
# mosquitto_sub -t 'sskaje/#'  -q 1 -h test.mosquitto.org
$mqtt->publish('sskaje/test', $msg, 0, 1, 0, 1);


$msg = str_repeat('привет ', 2);
# mosquitto_sub -t 'sskaje/#'  -q 1 -h test.mosquitto.org
$mqtt->publish('sskaje/test', $msg, 0, 1, 0, 1);
