<?php
/**
 *
 * PHP version 5.5
 *
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */
namespace MQTT;

/**
 * Debug class
 */
class spMQTTDebug
{
    protected static $enabled = false;
    public static function Enable()
    {
        self::$enabled = true;
    }
    public static function Disable()
    {
        self::$enabled = false;
    }
    public static function Log($message, $error_log=false)
    {
        list($usec, $sec) = explode(' ', microtime());
        $datetime = date('Y-m-d H:i:s', $sec);
        $log_msg = sprintf("[%s.%06d] %s \n", $datetime, $usec * 1000000, trim($message));
        if (self::$enabled) {
            echo $log_msg;
        }
        if ($error_log) {
            error_log($log_msg);
        }
    }

    /**
     * Print string in Hex
     *
     * @param string $chars
     * @param bool $return
     */
    public static function printHex($chars, $return=false)
    {
        $output = '';
        for ($i=0; isset($chars[$i]); $i++) {
            $output .= sprintf('%02x ', ord($chars[$i]));
        }
        if ($output) {
            $output .= "\n";
        }
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * Print string in Binary
     *
     * @param string $chars
     * @param bool $return
     */
    public static function printBin($chars, $return=false)
    {
        $output = '';
        for ($i=0; isset($chars[$i]); $i++) {
            $output .= sprintf('%08b ', ord($chars[$i]));
        }
        if ($output) {
            $output .= "\n";
        }
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}
