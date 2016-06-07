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

use MQTT\spMQTTMessageType;

/**
 * Message CONNACK
 */
class Connack extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::CONNACK;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;

    protected function processRead($message)
    {
        if (!isset($message[3])) {
            return false;
        }
        if (ord($message[0]) >> 4 == $this->message_type && $message[3] == chr(0)) {
            $this->mqtt->getLogger()->debug('Connected to Broker');
            return true;
        } else {
            $connect_errors = array(
                0 => 'Connection Accepted',
                1 => 'Connection Refused: unacceptable protocol version',
                2 => 'Connection Refused: identifier rejected',
                3 => 'Connection Refused: server unavailable',
                4 => 'Connection Refused: bad user name or password',
                5 => 'Connection Refused: not authorized',
            );
            $this->mqtt->getLogger()->error(sprintf(
                'Connection failed! (Error: 0x%02x 0x%02x|%s)',
                ord($message[0]),
                ord($message[3]),
                isset($connect_errors[ord($message[3])]) ? $connect_errors[ord($message[3])] : 'Unknown error'
            ));
            throw new \Exception(sprintf(
                "Connection failed! (Error: 0x%02x 0x%02x|%s)",
                ord($message[0]),
                ord($message[3]),
                isset($connect_errors[ord($message[3])]) ? $connect_errors[ord($message[3])] : 'Unknown error'
            ));
        }
    }
}
