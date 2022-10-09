<?php 
namespace AdinanCenci\DescriptivePlaylist\Utils;

class Helpers 
{
    public static function guidv4($data = null) : string
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function is($data, string $expectedType) : bool
    {
        if ($expectedType == 'string[]') {
            return self::isArrayOf($data, 'is_string');
        }

        if ($expectedType == 'int[]') {
            return self::isArrayOf($data, 'is_int');
        }

        if ($expectedType == 'numeric[]') {
            return self::isArrayOf($data, 'is_numeric');
        }

        if ($expectedType == 'alphanumeric') {
            return self::isAlphanumeric($data);
        }

        if ($expectedType == 'alphanumeric[]') {
            return self::isArrayOf($data, [get_called_class(), 'isAlphanumeric']);
        }

        if ($expectedType == 'null') {
            return is_null($data);
        }

        return gettype($data) == $expectedType;
    }


    public static function isArrayOf($data, $function) : bool
    {
        if (! is_array($data)) {
            return false;
        }

        foreach ($data as $v) {
            if (! call_user_func($function, $v)) {
                return false;
            }
        }

        return true;
    }


    public static function isAlphanumeric($data) : bool
    {
        return is_string($data) || is_numeric($data);
    }
}