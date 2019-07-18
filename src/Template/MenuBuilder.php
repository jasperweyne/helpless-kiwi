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
                'path' => 'admin_index',
                'title' => 'Dashboard',
                'sub' => [
                    [
                        'path' => 'admin_index',
                        'title' => 'Persoonlijk Dashboard',
                    ],
                    // array(
                    //     'path' => 'admin_index',
                    //     'title' => 'Prisma (Bestuur 10)' ,
                    // ),
                    // array(
                    //     'path' => 'admin_index',
                    //     'title' => 'Gaudium (Aco)' ,
                    // ),
                    // array(
                    //     'path' => 'admin_index',
                    //     'title' => 'CreaCie (CreaCie)' ,
                    // ),
                    // array(
                    //     'path' => 'admin_index',
                    //     'title' => 'Piratendispuut' ,
                    // ),
                ],
            ],
            // array(
            //     'path' => 'admin_index',
            //     'title' => 'Activiteiten',
            //     'activeCriteria' => 'null',
            // ),
            [
                'path' => 'app_logout',
                'title' => 'Uitloggen',
                'role' => 'ROLE_USER',
                'class' => 'mobile',
            ],
            [
                'title' => 'Beheer',
            ],
            // array(
            //     'path' => 'admin_index',
            //     'title' => 'E-mail',
            //     'activeCriteria' => 'null',
            // ),
            // array(
            //     'path' => 'admin_index',
            //     'title' => 'Statistieken',
            //     'activeCriteria' => 'null',
            // ),
            // [
            //     'path' => 'admin_person_index',
            //     'title' => 'Contacten',
            //     'activeCriteria' => 'admin_person',
            // ],
            // [
            //     'path' => 'admin_partner_index',
            //     'title' => 'Partners',
            //     'activeCriteria' => 'admin_partner',
            // ],
            // array(
            //     'path' => 'admin_inventory_index',
            //     'title' => 'Inventaris',
            //     'activeCriteria' => 'admin_inventory',
            // ),
            // array(
            //     'path' => 'admin_index',
            //     'title' => 'Financien',
            //     'activeCriteria' => 'null',
            // ),
            // array(
            //     'title' => 'Ontwikkeling'
            // ),
            // array(
            //     'path' => 'admin_index',
            //     'title' => 'Gebeurtenisoverzicht',
            //     'activeCriteria' => 'null',
            // ),
            // array(
            //     'path' => 'admin_index',
            //     'title' => 'Instellingen',
            //     'activeCriteria' => 'null',
            // ),
        ];
    }
}
