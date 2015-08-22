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
 * Message SUBACK
 */
class Unsuback extends  AbstractMessage
{
    protected $message_type = spMQTTMessageType::UNSUBACK;
    protected $protocol_type = self::FIXED_ONLY;
    protected $read_bytes = 4;
    protected function processRead($message)
    {
        $unsuback_packet = $this->processReadFixedHeaderWithMsgID($message);
        if (!$unsuback_packet) {
            return false;
        }
        return $unsuback_packet['msgid'];
    }
}
