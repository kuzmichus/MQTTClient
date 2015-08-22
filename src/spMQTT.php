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

use MQTT\Validators\CheckClientID;
use MQTT\Validators\CheckQos;

/**
 * Class spMQTT
 */
class spMQTT
{
    protected $clientid;
    protected $address;
    protected $socket;
    protected $keepalive = 60;
    protected $username = null;
    protected $password = null;
    protected $connect_clean = true;
    protected $connect_will = null;

    /**
     * Create spMQTTMessage object
     *
     * @param int $message_type
     * @return spMQTTMessage
     * @throws \Exception
     */
    public function getMessageObject($message_type)
    {
        if (!isset(spMQTTMessageType::$class[$message_type])) {
            throw new \Exception('Message type not defined', 100001);
        } else {
            return new spMQTTMessageType::$class[$message_type]($this);
        }
    }

    public function __construct($address, $clientid=null)
    {
        $this->address = $address;
        # check client id

        (new CheckClientID())->check($clientid);

        $this->clientid = $clientid;
    }

    /**
     * create socket
     * @return bool
     */
    protected function socket_connect()
    {
        spMQTTDebug::Log('socket_connect()');
        $context = stream_context_create();
        spMQTTDebug::Log('socket_connect(): connect to='.$this->address);

        $this->socket = stream_socket_client(
            $this->address,
            $errno,
            $errstr,
            60,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!$this->socket) {
            spMQTTDebug::Log("stream_socket_client() {$errno}, {$errstr}", true);
            return false;
        }
        stream_set_timeout($this->socket,  5);
        # MUST BE IN BLOCKING MODE
        stream_set_blocking($this->socket, true);

        return true;
    }

    /**
     * Send data
     *
     * @param string $packet
     * @param int $packet_size
     * @return int
     */
    public function socket_write($packet, $packet_size)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            return false;
        }
        return fwrite($this->socket, $packet, $packet_size);
    }

    /**
     * Read data
     *
     * @param int $length
     * @return string
     */
    public function socket_read($length = 8192)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            return false;
        }

        //	print_r(socket_get_status($this->socket));
        $string = "";
        $togo = $length;

        while (!feof($this->socket) && $togo>0) {
            $togo = $length - strlen($string);
            if ($togo) {
                $string .= fread($this->socket, $togo);
            }
        }

        return $string;
    }

    /**
     * Close socket
     *
     * @return bool
     */
    protected function socket_close()
    {
        if (is_resource($this->socket)) {
            spMQTTDebug::Log('socket_close()');
            return fclose($this->socket);
        }
    }

    /**
     * Reconnect connection
     *
     * @param bool $close_current close current existed connection
     * @return bool
     */
    public function reconnect($close_current=true)
    {
        spMQTTDebug::Log('reconnect()');
        if ($close_current) {
            spMQTTDebug::Log('reconnect(): close current');
            $this->disconnect();
            $this->socket_close();
        }

        return $this->connect();
    }

    /**
     * Set username/password
     *
     * @param string $username
     * @param string $password
     */
    public function setAuth($username=null, $password=null)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Set Keep Alive timer
     *
     * @param int $keepalive
     */
    public function setKeepalive($keepalive)
    {
        $this->keepalive = (int) $keepalive;
    }

    /**
     * Set Clean Session
     *
     * @param bool $clean
     */
    public function setConnectClean($clean)
    {
        $this->connect_clean = $clean ? true : false;
    }

    /**
     * Set Will message
     *
     * @param spMQTTWill $will
     */
    public function setWill(spMQTTWill $will)
    {
        $this->connect_will = $will;
    }

    /**
     * Connect to broker
     *
     * @return bool
     */
    public function connect()
    {
        # create socket resource
        if (!$this->socket_connect()) {
            return false;
        }
        spMQTTDebug::Log('connect()');

        $connectobj = $this->getMessageObject(spMQTTMessageType::CONNECT);

        if (!$this->connect_clean && empty($this->clientid)) {
            throw new \Exception('Client id must be provided if Clean Session flag is set false.', 100701);
        }

        # default client id
        if (empty($this->clientid)) {
            $clientid = 'mqtt'.substr(md5(uniqid('mqtt', true)), 8, 16);
        } else {
            $clientid = $this->clientid;
        }
        $connectobj->setClientID($clientid);
        spMQTTDebug::Log('connect(): clientid=' . $clientid);
        $connectobj->setKeepalive($this->keepalive);
        spMQTTDebug::Log('connect(): keepalive=' . $this->keepalive);
        $connectobj->setAuth($this->username, $this->password);
        spMQTTDebug::Log('connect(): username=' . $this->username . ' password=' . $this->password);
        $connectobj->setClean($this->connect_clean);
        if ($this->connect_will instanceof spMQTTWill) {
            $connectobj->setWill($this->connect_will);
        }

        $length = 0;
        $msg = $connectobj->build($length);

        $bytes_written = $connectobj->write();
        spMQTTDebug::Log('connect(): bytes written=' . $bytes_written);


        $connackobj = null;
        $connected = $connectobj->read(spMQTTMessageType::CONNACK, $connackobj);
        spMQTTDebug::Log('connect(): connected=' . ($connected ? 1 : 0));

        # save current time for ping ?

        return $connected;
    }

    /**
     * Publish message to topic
     *
     * @param string $topic
     * @param string $message
     * @param int $dup
     * @param int $qos
     * @param int $retain
     * @param int|null $msgid
     * @return array|bool
     */
    public function publish($topic, $message, $dup=0, $qos=0, $retain=0, $msgid=null)
    {
        spMQTTDebug::Log('publish()');
        $publishobj = $this->getMessageObject(spMQTTMessageType::PUBLISH);
        $publishobj->setTopic($topic);
        $publishobj->setMessage($message);
        $publishobj->setDup($dup);
        $publishobj->setQos($qos);
        $publishobj->setRetain($retain);
        $publishobj->setMsgID($msgid);

        $publish_bytes_written = $publishobj->write();
        spMQTTDebug::Log('publish(): bytes written=' . $publish_bytes_written);

        if ($qos == 0) {
            return array(
                'qos'   =>  $qos,
                'ret'   =>  $publish_bytes_written != false,
                'publish' =>  $publish_bytes_written,
            );
        } elseif ($qos == 1) {
            # QoS = 1, PUBLISH + PUBACK
            $pubackobj = null;
            $puback_msgid = $publishobj->read(spMQTTMessageType::PUBACK, $pubackobj);

            return array(
                'qos'   =>  $qos,
                'ret'   =>  $publish_bytes_written != false,
                'publish' =>  $publish_bytes_written,
                'puback'  =>  $puback_msgid,
            );
        } elseif ($qos == 2) {
            # QoS = 2, PUBLISH + PUBREC + PUBREL + PUBCOMP

            $pubrecobj = null;
            $pubrec_msgid = $publishobj->read(spMQTTMessageType::PUBREC, $pubrecobj);

            $pubrelobj = $this->getMessageObject(spMQTTMessageType::PUBREL);
            $pubrelobj->setMsgID($pubrec_msgid);
            $pubrel_bytes_written = $pubrelobj->write();

            $pubcompobj = null;
            $pubcomp_msgid = $pubrelobj->read(spMQTTMessageType::PUBCOMP, $pubcompobj);

            return array(
                'qos'   =>  $qos,
                'ret'   =>  $publish_bytes_written != false,
                'publish' =>  $publish_bytes_written,
                'pubrec'  =>  $pubrec_msgid,
                'pubrel'  =>  $pubrel_bytes_written,
                'pubcomp' =>  $pubcomp_msgid,
            );
        } else {
            return false;
        }
    }

    /**
     * SUBSCRIBE
     *
     * @param array $topics array(array(string topic, int qos, callback callback))
     * @param int $default_qos
     * @param null $default_callback
     */
    public function subscribe(array $topics)
    {
        foreach ($topics as $topic_name=>$topic_qos) {
            $this->topics_to_subscribe[$topic_name] = $topic_qos;
        }
        return true;
    }

    /**
     * Topics
     *
     * @var array
     */
    protected $topics = array();

    protected $topics_to_subscribe = array();
    protected $topics_to_unsubscribe = array();

    /**
     * SUBSCRIBE
     *
     * @param int $default_qos
     * @param null $default_callback
     */
    protected function do_subscribe()
    {
        # set msg id
        $msgid = mt_rand(1, 65535);
        # send SUBSCRIBE
        $subscribeobj = $this->getMessageObject(spMQTTMessageType::SUBSCRIBE);
        $subscribeobj->setMsgID($msgid);

        if (count($this->topics_to_subscribe) > 100) {
            throw new \Exception('Don\'t try to subscribe more than 100 topics', 100401);
        }

        $all_topic_qos = array();
        foreach ($this->topics_to_subscribe as $topic_name=>$topic_qos) {
            (new CheckQos())->check($topic_qos);

            $this->topics[$topic_name] = $topic_qos;

            $subscribeobj->addTopic(
                $topic_name,
                $topic_qos
            );
            $all_topic_qos[] = $topic_qos;
            unset($this->topics_to_subscribe[$topic_name]);
        }

        spMQTTDebug::Log('do_subscribe(): msgid=' . $msgid);
        $subscribe_bytes_written = $subscribeobj->write();
        spMQTTDebug::Log('do_subscribe(): bytes written=' . $subscribe_bytes_written);

//        # TODO: SUBACK+PUBLISH
//        # read SUBACK
//        $subackobj = null;
//        $suback_result = $subscribeobj->read(spMQTTMessageType::SUBACK, $subackobj);
//
//        # check msg id & qos payload
//        if ($msgid != $suback_result['msgid']) {
//            throw new SPMQTT_Exception('SUBSCRIBE/SUBACK message identifier mismatch: ' . $msgid . ':' . $suback_result['msgid'], 100402);
//        }
//        if ($all_topic_qos != $suback_result['qos']) {
//            throw new SPMQTT_Exception('SUBACK returned qos list doesn\'t match SUBSCRIBE', 100403);
//        }

        return array($msgid, $all_topic_qos);
    }


    /**
     * loop
     * @param callback $callback function(spMQTT $mqtt, $topic, $message)
     * @throws \Exception
     */
    public function loop($callback)
    {
        spMQTTDebug::Log('loop()');

        if (empty($this->topics) && empty($this->topics_to_subscribe)) {
            throw new \Exception('No topic subscribed/to be subscribed', 100601);
        }

        $last_subscribe_msgid = 0;
        $last_subscribe_qos = array();
        $last_unsubscribe_msgid = 0;
        while (1) {
            # Subscribe topics
            if (!empty($this->topics_to_subscribe)) {
                list($last_subscribe_msgid, $last_subscribe_qos) = $this->do_subscribe();
            }
            # Unsubscribe topics
            if (!empty($this->topics_to_unsubscribe)) {
                $last_unsubscribe_msgid = $this->do_unsubscribe();
            }

            $sockets = array($this->socket);
            $w = $e = null;

            if (stream_select($sockets, $w, $e, $this->keepalive / 2)) {
                if (feof($this->socket) || !$this->checkAndPing()) {
                    spMQTTDebug::Log('loop(): EOF detected');
                    $this->reconnect();
                    $this->subscribe($this->topics);
                }

                # The maximum value of remaining length is 268 435 455, FF FF FF 7F.
                # In most cases, 4 bytes is enough for fixed header and remaining length.
                # For PUBREL and UNSUBACK, 4 bytes is the maximum length.
                # For SUBACK, QoS list should be checked.
                # So, read the first 4 bytes and try to figure out the remaining length,
                # then read else.

                # read 4 bytes
                $read_bytes = 4;
                $read_message = $this->socket_read($read_bytes);
                if (empty($read_message)) {
                    continue;
                }

                $cmd = $this->unpackCommand(ord($read_message[0]));

                $message_type = $cmd['message_type'];
                $dup = $cmd['dup'];
                $qos = $cmd['qos'];
                $retain = $cmd['retain'];

                spMQTTDebug::Log("loop(): message_type={$message_type}, dup={$dup}, QoS={$qos}, RETAIN={$retain}");

                $flag_remaining_length_finished = 0;
                for ($i=1; isset($read_message[$i]); $i++) {
                    if (ord($read_message[$i]) < 0x80) {
                        $flag_remaining_length_finished = 1;
                        break;
                    }
                }
                if (empty($flag_remaining_length_finished)) {
                    # read 3 more bytes
                    $read_message .= $this->socket_read(3);
                }

                $pos = 1;
                $len = $pos;
                $remaining_length = $this->remainingLengthDecode($read_message, $pos);
                if ($flag_remaining_length_finished) {
                    $to_read = $remaining_length - (3 + $len - $pos);
                } else {
                    $to_read = $remaining_length - 2;
                }
                spMQTTDebug::Log('loop(): remaining length=' . $remaining_length . ' to read='.$to_read);

                $read_message .= $this->socket_read($to_read);

                spMQTTDebug::Log('loop(): read message=' . spMQTTDebug::printHex($read_message, true));

                switch ($message_type) {
                    # Process PUBLISH
                    case spMQTTMessageType::PUBLISH:
                        spMQTTDebug::Log('loop(): PUBLISH');
                        # topic length
                        $topic_length = $this->toUnsignedShort(substr($read_message, $pos, 2));
                        $pos += 2;
                        # topic content
                        $topic = substr($read_message, $pos, $topic_length);
                        $pos += $topic_length;

                        # PUBLISH QoS 0 doesn't have msgid
                        if ($qos > 0) {
                            $msgid = $this->toUnsignedShort(substr($read_message, $pos, 2));
                            $pos += 2;
                        }

                        # message content
                        $message = substr($read_message, $pos);

                        if ($qos == 0) {
                            spMQTTDebug::Log('loop(): PUBLISH QoS=0 PASS');
                            # Do nothing
                        } elseif ($qos == 1) {
                            # PUBACK
                            $pubackobj = $this->getMessageObject(spMQTTMessageType::PUBACK);
                            $pubackobj->setDup($dup);
                            $pubackobj->setMsgID($msgid);
                            $puback_bytes_written = $pubackobj->write();
                            spMQTTDebug::Log('loop(): PUBLISH QoS=1 PUBACK written=' . $puback_bytes_written);
                        } elseif ($qos == 2) {
                            # PUBREC
                            $pubrecobj = $this->getMessageObject(spMQTTMessageType::PUBREC);
                            $pubrecobj->setDup($dup);
                            $pubrecobj->setMsgID($msgid);
                            $pubrec_bytes_written = $pubrecobj->write();
                            spMQTTDebug::Log('loop(): PUBLISH QoS=2 PUBREC written=' . $pubrec_bytes_written);
                        } else {
                            # wrong packet
                            spMQTTDebug::Log('loop(): PUBLISH Invalid QoS');
                        }
                        # callback
                        call_user_func($callback, $this, $topic, $message);
                        break;

                    # Process PUBREL
                    case spMQTTMessageType::PUBREL:
                        spMQTTDebug::Log('loop(): PUBREL');
                        $msgid = $this->toUnsignedShort(substr($read_message, $pos, 2));
                        $pos += 2;

                        # PUBCOMP
                        $pubcompobj = $this->getMessageObject(spMQTTMessageType::PUBCOMP);
                        $pubcompobj->setDup($dup);
                        $pubcompobj->setMsgID($msgid);
                        $pubcomp_bytes_written = $pubcompobj->write();
                        spMQTTDebug::Log('loop(): PUBREL QoS=2 PUBCOMP written=' . $pubcomp_bytes_written);
                        break;

                    # Process SUBACK
                    case spMQTTMessageType::SUBACK:
                        spMQTTDebug::Log('loop(): SUBACK');
                        $msgid = $this->toUnsignedShort(substr($read_message, $pos, 2));
                        $pos += 2;

                        $qos_list = array();
                        for ($i=$pos; isset($read_message[$i]); $i++) {
                            # pick bit 0,1
                            $qos_list[] = ord($read_message[$i]) & 0x03;
                        }

                        # check msg id & qos payload
                        if ($msgid != $last_subscribe_msgid) {
                            spMQTTDebug::Log('loop(): SUBACK message identifier mismatch: ' . $msgid . ':' . $last_subscribe_msgid);
                        } else {
                            spMQTTDebug::Log('loop(): SUBACK msgid=' . $msgid);
                        }
                        if ($last_subscribe_qos != $qos_list) {
                            spMQTTDebug::Log('loop(): SUBACK returned qos list doesn\'t match SUBSCRIBE');
                        }

                        break;

                    # Process UNSUBACK
                    case spMQTTMessageType::UNSUBACK:
                        spMQTTDebug::Log('loop(): UNSUBACK');
                        $msgid = $this->toUnsignedShort(substr($read_message, $pos, 2));
                        $pos += 2;

                        # TODO:???
                        if ($msgid != $last_unsubscribe_msgid) {
                            spMQTTDebug::Log('loop(): UNSUBACK message identifier mismatch ' . $msgid . ':' . $last_unsubscribe_msgid);
                        } else {
                            spMQTTDebug::Log('loop(): UNSUBACK msgid=' . $msgid);
                        }

                        break;

                }
            }
        }
    }

    protected function checkAndPing()
    {
        spMQTTDebug::Log('checkAndPing()');
        static $time = null;
        $current_time = time();
        if (empty($time)) {
            $time = $current_time;
        }

        if ($current_time - $time >= $this->keepalive / 2) {
            spMQTTDebug::Log("checkAndPing(): current_time={$current_time}, time={$time}, keepalive={$this->keepalive}");
            $time = $current_time;
            $ping_result = $this->ping();
            return $ping_result;
        }
        return true;
    }

    /**
     * Unsubscribe topics
     *
     * @param array $topics
     * @return bool
     * @throws \Exception
     */
    public function unsubscribe(array $topics)
    {
        foreach ($topics as $topic) {
            $this->topics_to_unsubscribe[] = $topic;
        }
        return true;
    }
    /**
     * Unsubscribe topics
     *
     * @param array $topics
     * @return bool
     * @throws \Exception
     */
    protected function do_unsubscribe()
    {
        # set msg id
        $msgid = mt_rand(1, 65535);
        # send SUBSCRIBE
        $unsubscribeobj = $this->getMessageObject(spMQTTMessageType::UNSUBSCRIBE);
        $unsubscribeobj->setMsgID($msgid);

        foreach ($this->topics_to_unsubscribe as $tn=>$topic_name) {
            if (!isset($this->topics[$topic_name])) {
                # log
                continue;
            }

            $unsubscribeobj->addTopic($topic_name);
            unset($this->topics[$topic_name]);
            unset($this->topics_to_unsubscribe[$tn]);
        }

        $unsubscribe_bytes_written = $unsubscribeobj->write();
        spMQTTDebug::Log('unsubscribe(): bytes written=' . $unsubscribe_bytes_written);

        # read UNSUBACK
        $unsubackobj = null;
        $unsuback_msgid = $unsubscribeobj->read(spMQTTMessageType::UNSUBACK, $unsubackobj);

        # check msg id & qos payload
        if ($msgid != $unsuback_msgid) {
            throw new \Exception('UNSUBSCRIBE/UNSUBACK message identifier mismatch: ' . $msgid . ':' . $unsuback_msgid, 100502);
        }

        return true;
    }

    /**
     * Disconnect connection
     *
     * @return bool
     */
    public function disconnect()
    {
        spMQTTDebug::Log('disconnect()');
        $disconnectobj = $this->getMessageObject(spMQTTMessageType::DISCONNECT);
        return $disconnectobj->write();
    }

    /**
     * Send PINGREQ and check PINGRESP
     *
     * @return bool
     */
    public function ping()
    {
        spMQTTDebug::Log(__METHOD__);
        $pingreqobj = $this->getMessageObject(spMQTTMessageType::PINGREQ);
        $pingreqobj->write();
        $pingrespobj = null;
        $pingresp = $pingreqobj->read(spMQTTMessageType::PINGRESP, $pingrespobj);
        spMQTTDebug::Log('ping(): response ' . ($pingresp ? 1 : 0));
        return $pingresp;
    }

    /**
     * Convert WORD to unsigned short
     *
     * @param string $word
     * @return int
     */
    public function toUnsignedShort($word)
    {
        return (ord($word[0]) << 8) | (ord($word[1]));
    }

    /**
     * Decode Remaining Length
     *
     * @param string $msg
     * @param int & $i
     * @return int
     */
    public function remainingLengthDecode($msg, &$i)
    {
        $multiplier = 1;
        $value = 0 ;
        do {
            $digit = ord($msg[$i]);
            $value += ($digit & 0x7F) * $multiplier;
            $multiplier *= 0x80;
            $i++;
        } while (($digit & 0x80) != 0);
        return $value;
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
}
