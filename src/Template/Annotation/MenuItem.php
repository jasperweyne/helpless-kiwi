<?php

namespace App\Template\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class MenuItem
{
    /**
     * @Required
     *
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $menu;

    /**
     * @var string
     */
    public $role;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $activeCriteria;

    /**
     * @var int
     */
    public $order;

    /**
     * @var string
     */
    private $path;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getActiveCriteria()
    {
        return $this->activeCriteria;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
