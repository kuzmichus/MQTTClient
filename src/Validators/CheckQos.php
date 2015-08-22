<?php
/**
 *
 * PHP version 5.5
 *
 * @package MQTT\Validators
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */

namespace MQTT\Validators;

class CheckQos
{
    public function check($qos)
    {
        if ($qos > 2 || $qos < 0) {
            throw new \Exception('QoS must be an integer in (0,1,2).', 300001);
        }
    }
}
