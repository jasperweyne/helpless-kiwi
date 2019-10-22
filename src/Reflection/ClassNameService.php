<?php

namespace App\Reflection;

class ClassNameService
{
    public static function fqcnToName($fqcn)
    {
        if (preg_match('~([^\\\\]+)$~i', $fqcn, $matches)) {
            return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1 \\2', '\\1 \\2'], $matches[1]));
        }

        return $fqcn;
    }
}
