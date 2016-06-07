<?php
/**
 *
 * PHP version 5.5
 *
 * @author  Sergey V.Kuzin <sergey@kuzin.name>
 * @license MIT
 */
namespace MQTT;

use Psr\Log\LoggerInterface;

/**
 * Debug class
 */
class spMQTTDebug
{
    protected static $enabled = false;
    /** @var \Psr\Log\LoggerInterface */
    protected static $logger = null;

    public static function setLogger(LoggerInterface $logger)
    {
        static::$logger = $logger;
    }

    public static function Enable()
    {
        static::$enabled = true;
    }

    public static function Disable()
    {
        static::$enabled = false;
    }

    /**
     * Print string in Hex
     *
     * @param string $chars
     * @param bool $return
     */
    public static function printHex($chars, $return = false)
    {
        $output = '';
        for ($i = 0; isset($chars[$i]); $i++) {
            $output .= sprintf('%02x ', ord($chars[$i]));
        }
        if ($output) {
            $output .= PHP_EOL;
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
    public static function printBin($chars, $return = false)
    {
        $output = '';
        for ($i = 0; isset($chars[$i]); $i++) {
            $output .= sprintf('%08b ', ord($chars[$i]));
        }
        if ($output) {
            $output .= PHP_EOL;
        }
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}
