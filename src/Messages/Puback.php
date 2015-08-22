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

class Puback extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PUBACK;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;
    protected function processRead($message)
    {
        $puback_packet = $this->processReadFixedHeaderWithMsgID($message);
        if (!$puback_packet) {
            return false;
        }
        return $puback_packet['msgid'];
    }
    protected $msgid;
    public function setMsgID($msgid)
    {
        $this->msgid = $msgid;
    }
    public function setDup($dup)
    {
        return $this->header->setDup($dup);
    }
    protected function processBuild()
    {
        ;
        $buffer = "";
        $buffer .= pack('n', $this->msgid);
        spMQTTDebug::Log('Message PUBACK: msgid='.$this->msgid);
        $this->header->setQos(1);
        return $buffer;
    }
}
