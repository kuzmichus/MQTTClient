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

class Subscribe extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::SUBSCRIBE;
    protected $protocol_type = self::WITH_PAYLOAD;
    protected $topics = array();
    protected $msgid = 0;
    public function addTopic($topic, $qos)
    {
        $this->topics[$topic] = $qos;
    }
    public function setMsgID($msgid)
    {
        $this->msgid = $msgid;
    }
    protected function processBuild()
    {
        ;
        $buffer = "";
        # Variable Header: message identifier
        $buffer .= pack('n', $this->msgid);
        spMQTTDebug::Log('Message SUBSCRIBE: msgid='.$this->msgid);
        # Payload
        foreach ($this->topics as $topic=>$qos) {
            $topic_length = strlen($topic);
            $buffer .= pack('n', $topic_length);
            $buffer .= $topic;
            $buffer .= chr($qos);
        }
        # SUBSCRIBE uses QoS 1
        $this->header->setQos(1);
        #
        $this->header->setDup(0);
        return $buffer;
    }
}
