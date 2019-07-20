<?php

namespace App\Settings;

class DotEnvService
{
    public function read()
    {
        return $_ENV;
    }

    public function write(array $settings)
    {
        // Create an array of modified values
        $diff = array_diff_assoc($settings, $this->read());

        // Check if a value already occurs in .env.local, if so, edit the value at that location

        // Append all other values to the end
    }
}
