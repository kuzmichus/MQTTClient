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

class Publish extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PUBLISH;
    protected $protocol_type = self::WITH_PAYLOAD;
    protected $topic;
    protected $message;
    protected $msgid = 0;
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }
    public function setMessage($message)
    {
        $this->message = $message;
    }
    public function setMsgID($msgid)
    {
        $this->msgid = $msgid;
    }
    public function setDup($dup)
    {
        return $this->header->setDup($dup);
    }
    public function setQos($qos)
    {
        return $this->header->setQos($qos);
    }
    public function setRetain($retain)
    {
        return $this->header->setRetain($retain);
    }
    protected function processBuild()
    {
        ;
        $buffer = "";
        # Topic
        $buffer .= $this->packStringWithLength($this->topic);
        spMQTTDebug::Log('Message PUBLISH: topic='.$this->topic);
        spMQTTDebug::Log('Message PUBLISH: QoS='.$this->header->getQos());
        # Message ID if QoS > 0
        if ($this->header->getQos()) {
            if ($this->msgid !== null) {
                $id = (int) $this->msgid;
            } else {
                $id = ++$this->msgid;
            }
            $buffer .= pack('n', $id);
            spMQTTDebug::Log('Message PUBLISH: msgid='.$id);
        }
        # Payload
        $buffer .= $this->message;
        spMQTTDebug::Log('Message PUBLISH: message='.$this->message);
        return  $buffer;
    }
}
