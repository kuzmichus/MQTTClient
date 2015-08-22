<?php
/**
 *
 * PHP version 5.5
 *
 * @package MQTT\Messages
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */

namespace MQTT\Messages;

use MQTT\spMQTTDebug;
use MQTT\spMQTTMessageType;
use MQTT\spMQTTUtil;

class Pingresp extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PINGRESP;
    protected $protocol_type = self::FIXED_ONLY;
    protected $read_bytes = 2;
    protected function processRead($message)
    {
        # for PINGRESP
        if (!isset($message[$this->read_bytes - 1])) {
            # error
            spMQTTDebug::Log('Message PINGRESP: error on reading');
            return false;
        }
        $packet = unpack('Ccmd/Clength', $message);
        $packet['cmd'] = $this->unpackCommand($packet['cmd']);
        if ($packet['cmd']['message_type'] != $this->message_type) {
            spMQTTDebug::Log("Message PINGRESP: type mismatch");
            return false;
        } else {
            spMQTTDebug::Log("Message PINGRESP: success");
            return true;
        }
    }
}
