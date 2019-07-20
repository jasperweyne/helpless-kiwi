<?php

namespace App\Template;

class MenuBuilder
{
    /**
     * @var MenuDiscovery
     */
    private $discovery;

    public function __construct(MenuDiscovery $discovery)
    {
        $this->discovery = $discovery;
    }

    /**
     * Returns a list of available menu items.
     *
     * @return array
     */
    public function getItems(string $menu = '')
    {
        $mapped = ($menu == 'admin') ? $this->getMain() : array();

        $items = $this->discovery->getMenuItems($menu);
        foreach ($items as $item) {
            $arr = [
                'title' => $item->getTitle(),
                'path' => $item->getPath(),
            ];
            if (null !== $item->getRole()) {
                $arr['role'] = $item->getRole();
            }
            if (null !== $item->getClass()) {
                $arr['class'] = $item->getClass();
            }
            if (null !== $item->getActiveCriteria()) {
                $arr['activeCriteria'] = $item->getActiveCriteria();
            }
            $mapped[] = $arr;
        }

        return $mapped;
    }

    public function getMain()
    {
        return [
            [
                'path' => 'activity_index',
                'title' => 'Terug naar frontend',
                'activeCriteria' => 'null',
            ],
            [
                'title' => 'Beheer',
            ],
        ];
    }
}
