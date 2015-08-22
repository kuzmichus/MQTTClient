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
use MQTT\spMQTTUtil;

class Connect extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::CONNECT;
    protected $protocol_type = self::WITH_PAYLOAD;
    /**
     * @var spMQTTWill
     */
    protected $will;
    protected $clean = 1;
    protected $username = null;
    protected $password = null;
    protected $keepalive = 60;
    protected $clientid = '';
    public function setClean($clean)
    {
        $this->clean = $clean ? 1 : 0;
    }
    public function setWill(\spMQTTWill $will)
    {
        $this->will = $will;
    }
    public function setAuth($username, $password=null)
    {
        $this->username = $username;
        $this->password = $password;
    }
    public function setKeepalive($keepalive)
    {
        $this->keepalive = (int) $keepalive;
    }
    public function setClientID($clientid)
    {
        $this->clientid = $clientid;
    }
    protected function processBuild()
    {
        ;
        $buffer = "";
        $buffer .= chr(0x00); # 0x00
        $buffer .= chr(0x06); # 0x06
        $buffer .= chr(0x4d); # 'M'
        $buffer .= chr(0x51); # 'Q'
        $buffer .= chr(0x49); # 'I'
        $buffer .= chr(0x73); # 's'
        $buffer .= chr(0x64); # 'd'
        $buffer .= chr(0x70); # 'p'
        $buffer .= chr(0x03); # protocol version
        # Connect Flags
        # Set to 0 by default
        $var = 0;
        # clean session
        if ($this->clean) {
            $var|= 0x02;
        }
        # Will flags
        if ($this->will) {
            $var |= $this->will->get();
        }
        # User name flag
        if ($this->username != null) {
            $var |= 0x80;
        }
        # Password flag
        if ($this->password != null) {
            $var |= 0x40;
        }
        $buffer .= chr($var);
        # End of Connect Flags
        # Keep alive: unsigned short 16bits big endian
        $buffer .= pack('n', $this->keepalive);
        # Append client id
        $buffer .= $this->packStringWithLength($this->clientid);
        # Adding will to payload
        if ($this->will != null) {
            $buffer .= $this->packStringWithLength($this->will->getTopic());
            $buffer .= $this->packStringWithLength($this->will->getMessage());
        }
        # Append User name
        if ($this->username) {
            $buffer .= $this->packStringWithLength($this->username);
        }
        # Append Password
        if ($this->password) {
            $buffer .= $this->packStringWithLength($this->password);
        }
        return $buffer;
    }
}
