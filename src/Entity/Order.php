<?php

namespace App\Entity;

class Order
{
    /**
     * @var int[]
     */
    private $data;

    /** @param int[] $data */
    protected function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return self::fromOrder($this->data);
    }

    public static function avg(Order $low, Order $high): Order
    {
        return self::calc($low, $high, function ($x, $y) {
            return ($x + $y) / 2;
        });
    }

    public static function create(string $s): Order
    {
        return new Order(self::toOrder($s));
    }

    public static function calc(Order $a, Order $b, callable $fn): Order
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
            $val = $interm[$i] + $carry;
            $res[$i] = $val % 26;

            // Set carry for the next iteration
            $carry = floor($val / 26);
        }

        // Return result
        return new Order($res);
    }

    /** @return array<int, int> */
    protected static function toOrder(string $data): array
    {
        $strToIdx = function ($x) {
            return ord($x) - ord('a');
        };

        return array_map($strToIdx, str_split($data));
    }

    /** @param int[] $data */
    protected static function fromOrder(array $data): string
    {
        $idxToStr = function ($x) {
            return chr($x + ord('a'));
        };

        return implode(array_map($idxToStr, $data));
    }
}
