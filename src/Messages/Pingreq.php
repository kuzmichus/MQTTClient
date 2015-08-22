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

class Pingreq extends AbstractMessage
{
    protected $message_type = spMQTTMessageType::PINGREQ;
    protected $protocol_type = self::FIXED_ONLY;
}
