<?php
/**
 *
 * PHP version 5.5
 *
 * @package MQTT
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */

namespace MQTT;

use MQTT\Validators\CheckQos;

/**
 * Fixed Header
 */
class spMQTTMessageHeader
{
    protected $message_type = 0;
    protected $remaining_length = 0;
    protected $remaining_length_bytes = '';
    protected $dup = 0;
    protected $qos = 0;
    protected $retain = 0;
    public function __construct($message_type)
    {
        $this->message_type = (int) $message_type;
    }
    public function setDup($dup)
    {
        $this->dup = $dup ? 1 : 0;
    }
    public function getDup()
    {
        return $this->dup;
    }
    public function setQos($qos)
    {
        (new CheckQos())->check($qos);
        $this->qos = (int) $qos;
    }
    public function getQos()
    {
        return $this->qos;
    }
    public function setRetain($retain)
    {
        $this->retain = $retain ? 1 : 0;
    }
    public function getRetain()
    {
        return $this->retain;
    }
    public function setRemainingLength($remaining_length)
    {
        $this->remaining_length = $remaining_length;
        $this->remaining_length_bytes = $this->remainingLengthEncode($this->remaining_length);
    }
    /**
     * Build fixed header packet
     *
     * @return string
     */
    public function build()
    {
        $cmd = $this->message_type << 4;
        $cmd |= ($this->dup << 3);
        $cmd |= ($this->qos << 1);
        $cmd |= $this->retain;
        return chr($cmd) . $this->remaining_length_bytes;
    }
    public function getLength()
    {
        return 1 + strlen($this->remaining_length_bytes);
    }

    /**
     * Encode Remaining Length
     *
     * @param int $int
     * @return string
     */
    public function remainingLengthEncode($length)
    {
        $string = '';
        do {
            $digit = $length % 0x80;
            $length = $length >> 7;
            // if there are more digits to encode, set the top bit of this digit
            if ($length > 0) {
                $digit = ($digit | 0x80);
            }
            $string .= chr($digit);
        } while ($length > 0);
        return $string;
    }
}
