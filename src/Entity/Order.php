<?php

namespace App\Entity;

class Order
{
    private $data;

    protected function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return self::fromOrder($this->data);
    }

    public static function avg(Order $low, Order $high)
    {
        return self::calc($low, $high, function ($x, $y) {
            return ($x + $y) / 2;
        });
    }

    public static function create(string $s)
    {
        return new Order(self::toOrder($s));
    }

    public static function calc(Order $a, Order $b, $fn)
    {
        // Pad arrays to have equal length
        $length = max(count($a->data), count($b->data));
        $ia = array_pad($a->data, $length, 0);
        $ib = array_pad($b->data, $length, 0);

        // Apply $fn, carrying decimal value to next element
        $carry = 0;
        $interm = [];
        for ($i = 0; $i < $length; ++$i) {
            $val = ($fn)($ia[$i], $ib[$i]);

            // Calculate result
            $interm[$i] = floor($val) + $carry;

            // Set carry for the next iteration
            $carry = floor(($val - floor($val)) * 26);
        }

        // Reverse carry for overflowing elements
        $carry = 0;
        $res = $interm;
        for ($i = $length - 1; $i >= 0; --$i) {
            // Calculate result
            $res[$i] = ($interm[$i] + $carry) % 26;

            // Set carry for the next iteration
            $carry = floor($interm[$i] / 26);
        }

        // Return result
        return new Order($res);
    }

    protected static function toOrder(string $data)
    {
        $strToIdx = function ($x) {
            return ord($x) - ord('a');
        };

        return array_map($strToIdx, str_split($data));
    }

    protected static function fromOrder(array $data)
    {
        $idxToStr = function ($x) {
            return chr($x + ord('a'));
        };

        return implode(array_map($idxToStr, $data));
    }
}
