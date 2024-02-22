<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Constant path string of the project directory.
     */
    private readonly string $projectDir;

    public function __construct(string $environment, bool $debug)
    {
        // project directory is the parent folder of current directory `./src`
        $this->projectDir = dirname(__DIR__);

        parent::__construct($environment, $debug);
    }

    public function getProjectDir(): string
    {
        // also works in the absence of composer.json, as opposed to parent implementation
        return $this->projectDir;
    }
}
