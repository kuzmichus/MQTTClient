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

/**
 * Message PUBREL
 */
class Pubrel extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PUBREL;
    protected $protocol_type = self::WITH_VARIABLE;
    protected function processRead($message)
    {
        $pubrel_packet = $this->processReadFixedHeaderWithMsgID($message);
        if (!$pubrel_packet) {
            return false;
        }
        return $pubrel_packet['msgid'];
    }
    protected $msgid = 0;
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
        spMQTTDebug::Log('Message PUBREL: msgid='.$this->msgid);
        $this->header->setQos(1);
        return $buffer;
    }
}
