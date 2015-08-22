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

use MQTT\Messages\Connack;
use MQTT\Messages\Connect;
use MQTT\Messages\Disconnect;
use MQTT\Messages\Pingreq;
use MQTT\Messages\Pingresp;
use MQTT\Messages\Puback;
use MQTT\Messages\Pubcomp;
use MQTT\Messages\Publish;
use MQTT\Messages\Pubrec;
use MQTT\Messages\Pubrel;
use MQTT\Messages\Suback;
use MQTT\Messages\Subscribe;
use MQTT\Messages\Unsuback;
use MQTT\Messages\Unsubscribe;

/**
 * Message type definitions
 */
class spMQTTMessageType
{
    /**
     * Message Type: CONNECT
     */
    const CONNECT       = 0x01;
    /**
     * Message Type: CONNACK
     */
    const CONNACK       = 0x02;
    /**
     * Message Type: PUBLISH
     */
    const PUBLISH       = 0x03;
    /**
     * Message Type: PUBACK
     */
    const PUBACK        = 0x04;
    /**
     * Message Type: PUBREC
     */
    const PUBREC        = 0x05;
    /**
     * Message Type: PUBREL
     */
    const PUBREL        = 0x06;
    /**
     * Message Type: PUBCOMP
     */
    const PUBCOMP       = 0x07;
    /**
     * Message Type: SUBSCRIBE
     */
    const SUBSCRIBE     = 0x08;
    /**
     * Message Type: SUBACK
     */
    const SUBACK        = 0x09;
    /**
     * Message Type: UNSUBSCRIBE
     */
    const UNSUBSCRIBE   = 0x0A;
    /**
     * Message Type: UNSUBACK
     */
    const UNSUBACK      = 0x0B;
    /**
     * Message Type: PINGREQ
     */
    const PINGREQ       = 0x0C;
    /**
     * Message Type: PINGRESP
     */
    const PINGRESP      = 0x0D;
    /**
     * Message Type: DISCONNECT
     */
    const DISCONNECT    = 0x0E;
    public static $class = array(
        spMQTTMessageType::CONNECT      => Connect::class,
        spMQTTMessageType::CONNACK      => Connack::class,
        spMQTTMessageType::PUBLISH      => Publish::class,
        spMQTTMessageType::PUBACK       => Puback::class,
        spMQTTMessageType::PUBREC       => Pubrec::class,
        spMQTTMessageType::PUBREL       => Pubrel::class,
        spMQTTMessageType::PUBCOMP      => Pubcomp::class,
        spMQTTMessageType::SUBSCRIBE    => Subscribe::class,
        spMQTTMessageType::SUBACK       => Suback::class,
        spMQTTMessageType::UNSUBSCRIBE  => Unsubscribe::class,
        spMQTTMessageType::UNSUBACK     => Unsuback::class,
        spMQTTMessageType::PINGREQ      => Pingreq::class,
        spMQTTMessageType::PINGRESP     => Pingresp::class,
        spMQTTMessageType::DISCONNECT   => Disconnect::class,
    );
    public static $name = array(
        spMQTTMessageType::CONNECT      => 'CONNECT',
        spMQTTMessageType::CONNACK      => 'CONNACK',
        spMQTTMessageType::PUBLISH      => 'PUBLISH',
        spMQTTMessageType::PUBACK       => 'PUBACK',
        spMQTTMessageType::PUBREC       => 'PUBREC',
        spMQTTMessageType::PUBREL       => 'PUBREL',
        spMQTTMessageType::PUBCOMP      => 'PUBCOMP',
        spMQTTMessageType::SUBSCRIBE    => 'SUBSCRIBE',
        spMQTTMessageType::SUBACK       => 'SUBACK',
        spMQTTMessageType::UNSUBSCRIBE  => 'UNSUBSCRIBE',
        spMQTTMessageType::UNSUBACK     => 'UNSUBACK',
        spMQTTMessageType::PINGREQ      => 'PINGREQ',
        spMQTTMessageType::PINGRESP     => 'PINGRESP',
        spMQTTMessageType::DISCONNECT   => 'DISCONNECT',
    );
}
