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

/**
 * Message PUBREC
 */
class Pubrec extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PUBREC;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;
    protected $msgid;


    protected function processRead($message)
    {
        $pubrec_packet = $this->processReadFixedHeaderWithMsgID($message);
        if (!$pubrec_packet) {
            return false;
        }
        return $pubrec_packet['msgid'];
    }

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
        spMQTTDebug::Log('Message PUBREC: msgid='.$this->msgid);
        $this->header->setQos(1);
        return $buffer;
    }
}
