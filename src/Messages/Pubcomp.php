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
 * Message PUBCOMP
 */
class Pubcomp extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PUBCOMP;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;

    protected function processRead($message)
    {
        $pubcomp_packet = $this->processReadFixedHeaderWithMsgID($message);
        if (!$pubcomp_packet) {
            return false;
        }
        return $pubcomp_packet['msgid'];
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
        $buffer = '';
        $buffer .= pack('n', $this->msgid);
        $this->mqtt->getLogger()->debug('Message PUBCOMP: msgid=' . $this->msgid);
        $this->header->setQos(1);
        return $buffer;
    }
}
