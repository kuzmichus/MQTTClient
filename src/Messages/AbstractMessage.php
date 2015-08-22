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

use MQTT\spMQTT;
use MQTT\spMQTTDebug;
use MQTT\spMQTTMessageHeader;
use MQTT\spMQTTMessageType;
use MQTT\spMQTTUtil;

abstract class AbstractMessage
{
    /**
     * @var spMQTT
     */
    protected $mqtt;
    const FIXED_ONLY     = 0x01;
    const WITH_VARIABLE  = 0x02;
    const WITH_PAYLOAD   = 0x03;
    const MSGID_ONLY     = 0x04;
    protected $protocol_type = self::FIXED_ONLY;
    protected $read_bytes = 0;
    /**
     * @var spMQTTMessageHeader
     */
    protected $header = null;
    /**
     * Message Type
     *
     * @var int
     */
    protected $message_type = 0;
    public function __construct(spMQTT $mqtt)
    {
        $this->mqtt = $mqtt;
        $this->header = new spMQTTMessageHeader($this->message_type);
    }
    /**
     * Build packet data
     * @param int & $length
     * @return string
     * @throws Exception
     */
    final public function build(&$length)
    {
        if ($this->protocol_type == self::FIXED_ONLY) {
            $message = $this->processBuild();
        } elseif ($this->protocol_type == self::WITH_VARIABLE) {
            $message = $this->processBuild();
        } elseif ($this->protocol_type == self::WITH_PAYLOAD) {
            $message = $this->processBuild();
        } else {
            throw new \Exception('Invalid protocol type', 200003);
        }
        $length = strlen($message);
        $this->header->setRemainingLength($length);
        spMQTTDebug::Log('Message Build: remaining length='.$length);
        $length += $this->header->getLength();
        return $this->header->build() . $message;
    }
    protected function processBuild()
    {
        return '';
    }
    protected function processRead($message)
    {
        return false;
    }
    /**
     * Read packet to generate an new message object
     *
     * @param int $message_type  Message type
     * @param spMQTTMessage & $class
     * @return mixed
     */
    final public function read($message_type=null, & $class=null)
    {
        if (!empty($message_type)) {
            spMQTTDebug::Log('Message Read: message_type='.$message_type);
            # create message type
            $class = $this->mqtt->getMessageObject($message_type);
        } else {
            spMQTTDebug::Log('Message Read: message_type='.$this->message_type);
            $class = $this;
        }
        spMQTTDebug::Log('Message Read: bytes to read='.$class->read_bytes);
        if ($class->read_bytes) {
            $message = $this->mqtt->socket_read($class->read_bytes);
        } else {
            $message = $this->mqtt->socket_read(8192);
        }
        spMQTTDebug::Log('Message read: message=' . spMQTTDebug::printHex($message, true));
        spMQTTDebug::Log('Message Read: bytes to read='.$class->read_bytes);
        if (!method_exists($class, 'processRead')) {
            throw new \Exception('"processRead($message)" not defined in '. get_class($class), 200201);
        }
        if ($class->protocol_type == self::FIXED_ONLY) {
            return $class->processRead($message);
        } elseif ($class->protocol_type == self::WITH_VARIABLE) {
            return $class->processRead($message);
        } elseif ($class->protocol_type == self::WITH_PAYLOAD) {
            return $class->processRead($message);
        } else {
            throw new \Exception('Invalid protocol type', 200202);
        }
    }
    /**
     * Process packet with Fixed Header + Message Identifier only
     *
     * @param string $message
     * @return array|bool
     */
    final protected function processReadFixedHeaderWithMsgID($message)
    {
        $packet_length = 4;
        $name = spMQTTMessageType::$name[$this->message_type];
        if (!isset($message[$packet_length - 1])) {
            # error
            spMQTTDebug::Log("Message {$name}: error on reading");
            return false;
        }
        $packet = unpack('Ccmd/Clength/nmsgid', $message);
        $packet['cmd'] = $this->unpackCommand($packet['cmd']);
        if ($packet['cmd']['message_type'] != $this->message_type) {
            spMQTTDebug::Log("Message {$name}: type mismatch");
            return false;
        } else {
            spMQTTDebug::Log("Message {$name}: success");
            return $packet;
        }
    }
    /**
     * Send packet
     *
     * @return int
     */
    public function write()
    {
        spMQTTDebug::Log('Message write: message_type='.$this->message_type);
        $length = 0;
        $message = $this->build($length);
        $bytes_written = $this->mqtt->socket_write($message, $length);
        spMQTTDebug::Log('Message write: message=' . spMQTTDebug::printHex($message, true));
        spMQTTDebug::Log('Message write: bytes written='.$bytes_written);
        return $bytes_written;
    }

    /**
     * Unpack command
     * @param int $cmd
     * @return array
     */
    public function unpackCommand($cmd)
    {
        # check message type
        $message_type = $cmd >> 4;
        $dup = ($cmd & 0x08) >> 3;
        $qos = ($cmd & 0x06) >> 1;
        $retain = ($cmd & 0x01);
        return array(
            'message_type'  =>  $message_type,
            'dup'           =>  $dup,
            'qos'           =>  $qos,
            'retain'        =>  $retain,
        );
    }

    /**
     * return string with a 16-bit big endian length ahead.
     *
     * @param string $str  input string
     * @return string      returned string
     */
    public function packStringWithLength($str)
    {
        $len = strlen($str);
        return pack('n', $len) . $str;
    }
}
