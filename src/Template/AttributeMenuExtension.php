<?php

namespace App\Template;

use App\Template\Attribute\MenuItem;
use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\Annotation\Route;

// ToDo: implement caching
/**
 * @phpstan-import-type MenuItemArray from MenuExtensionInterface
 */
class AttributeMenuExtension implements MenuExtensionInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $directory;

    /**
     * The Kernel root directory.
     *
     * @var string
     */
    private $projectDir;

    /**
     * @var array<string, MenuItem[]>
     */
    private $menuItems = [];

    /**
     * MenuDiscovery constructor.
     *
     * @param string $namespace
     *                          The namespace of the menu items
     * @param string $directory
     *                          The directory of the menu items
     */
    public function __construct(string $namespace, string $directory, string $projectDir)
    {
        $this->namespace = $namespace;
        $this->directory = $directory;
        $this->projectDir = $projectDir;
    }

    /**
     * Returns all the menu items.
     *
     * @return MenuItemArray[]
     */
    public function getMenuItems(string $menu = ''): array
    {
        if (count($this->menuItems) === 0) {
            $this->discoverMenuItems();
        }

        if (!array_key_exists($menu, $this->menuItems)) {
            return [];
        }

        $mapped = [];
        foreach ($this->menuItems[$menu] as $item) {
            $mapped[] = $item->toMenuItemArray();
        }

        return $mapped;
    }

    /**
     * Discovers menu items.
     */
    private function discoverMenuItems(): void
    {
        // Iterate over all php files in the specified directory
        $path = $this->projectDir.'/'.$this->directory;
        $finder = new Finder();
        $finder->files()->name('*.php')->in($path);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $namespace = $file->getRelativePath() ? '\\'.strtr($file->getRelativePath(), '/', '\\') : '';
            $class = $this->namespace.$namespace.'\\'.$file->getBasename('.php');
            $reflClass = new \ReflectionClass($class);

            // Check for a Symfony route prefix defined at class level
            $routePrefix = '';
            $classRoutes = $reflClass->getAttributes(Route::class);
            if (count($classRoutes) > 0) {
                /** @var Route $classRoute */
                $classRoute = reset($classRoutes)->newInstance();
                $routePrefix = $classRoute->getName();
            }

            // Find and add MenuItem attribute(s) for each method to the index
            foreach ($reflClass->getMethods() as $method) {
                foreach ($method->getAttributes(MenuItem::class) as $reflMethod) {
                    /** @var MenuItem $attribute */
                    $attribute = $reflMethod->newInstance();

                    // If no path set, extract it from the Route attribute
                    if (null === $attribute->path) {
                        $routes = $method->getAttributes(Route::class);
                        if (count($routes) === 0) {
                            throw AnnotationException::semanticalError('A Symfony\Component\Routing\Annotation\Route attribute is required when using a App\Template\Attribute\MenuItem attribute');
                        }

                        /** @var Route $route */
                        $route = reset($routes)->newInstance();
                        $attribute->path = $routePrefix.$route->getName();
                    }

                    // Add the menu item to the index
                    $menu = $attribute->menu ?? '';
                    if (!array_key_exists($menu, $this->menuItems)) {
                        $this->menuItems[$menu] = [];
                    }
                    $this->menuItems[$menu][] = $attribute;
                }
            }
        }
    }
}
