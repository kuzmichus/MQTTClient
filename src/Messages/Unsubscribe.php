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

class Unsubscribe extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::UNSUBSCRIBE;
    protected $protocol_type = self::WITH_PAYLOAD;
    protected $topics = array();
    protected $msgid = 0;

    public function addTopic($topic)
    {
        $this->topics[] = $topic;
    }

    public function setMsgID($msgid)
    {
        $this->msgid = $msgid;
    }

    protected function processBuild()
    {
        $buffer = '';
        # Variable Header: message identifier
        $buffer .= pack('n', $this->msgid);
        $this->mqtt->getLogger()->debug('Message UNSUBSCRIBE: msgid=' . $this->msgid);
        # Payload
        foreach ($this->topics as $topic) {
            $topic_length = strlen($topic);
            $buffer .= pack('n', $topic_length);
            $buffer .= $topic;
        }
        # SUBSCRIBE uses QoS 1
        $this->header->setQos(1);
        $this->header->setDup(0);
        return $buffer;
    }
}
