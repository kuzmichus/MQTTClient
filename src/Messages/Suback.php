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

class Suback extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::SUBACK;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;
    protected function processRead($message)
    {
        $suback_packet = $this->processReadFixedHeaderWithMsgID($message);
        if (!$suback_packet) {
            return false;
        }
        $bytes = $this->mqtt->socket_read($suback_packet['length'] - 2);
        $return_qos = array();
        for ($i=0; isset($bytes[$i]); $i++) {
            # pick bit 0,1
            $return_qos[] = ord($bytes[$i]) & 0x03;
        }
        return array(
            'msgid' =>  $suback_packet['msgid'],
            'qos'   =>  $return_qos,
        );
    }
}
