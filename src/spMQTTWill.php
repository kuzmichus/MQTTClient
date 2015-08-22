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
 * Connect will
 */
class spMQTTWill
{
    protected $retain = 0;
    protected $qos = 0;
    protected $flag = 0;
    protected $topic = '';
    protected $message = '';
    public function __construct($flag=1, $qos=1, $retain=0, $topic='', $message='')
    {
        $this->flag    = $flag ? 1 : 0;
        (new CheckQos)->check($qos);
        $this->qos     = (int) $qos;
        $this->retain  = $retain ? 1 : 0;
        $this->topic   = $topic;
        $this->message = $message;
    }
    public function getTopic()
    {
        return $this->topic;
    }
    public function getMessage()
    {
        return $this->message;
    }
    /**
     *
     * @return int
     */
    public function get()
    {
        $var = 0;
        if ($this->flag) {
            # Will flag
            $var |= 0x04;
            # Will QoS
            $var |= $this->qos << 3;
            # Will RETAIN
            if ($this->retain) {
                $var |= 0x20;
            }
        }
        return $var;
    }
}
