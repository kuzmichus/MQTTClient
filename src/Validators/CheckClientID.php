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

/**
 * Class CheckClientID
 *
 * PHP version 5.5
 *
 * @package MQTT\Validators
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 */
class CheckClientID
{
    /**
     * @param $clientid
     * @return bool
     */
    public function check($clientid)
    {
        if (strlen($clientid) > 23) {
            throw new \UnexpectedValueException('Client identifier exceeds 23 bytes.', 2);
        }
        return true;
    }
}
