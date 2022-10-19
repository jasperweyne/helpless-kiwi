<?php

namespace App\Template;

use App\Template\Annotation\MenuItem;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

// ToDo: implement caching
/**
 * @phpstan-import-type MenuItemArray from MenuExtensionInterface
 */
class AnnotationMenuExtension implements MenuExtensionInterface
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
     * @var Reader
     */
    private $annotationReader;

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
    public function __construct(string $namespace, string $directory, string $projectDir, Reader $annotationReader)
    {
        $this->namespace = $namespace;
        $this->annotationReader = $annotationReader;
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
        if (!$this->menuItems) {
            $this->discoverMenuItems();
        }

        if (!array_key_exists($menu, $this->menuItems)) {
            return [];
        }

        $mapped = [];
        foreach ($this->menuItems[$menu] as $item) {
            $arr = [
                'title' => $item->getTitle(),
                'path' => (string) $item->getPath(),
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
            if (null !== $item->getOrder()) {
                $arr['order'] = $item->getOrder();
            }
            $mapped[] = $arr;
        }

        return $mapped;
    }

    /**
     * Discovers menu items.
     */
    private function discoverMenuItems(): void
    {
        $path = $this->projectDir.'/'.$this->directory;
        $finder = new Finder();
        $finder->files()->name('*.php')->in($path);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $namespace = $file->getRelativePath() ? '\\'.strtr($file->getRelativePath(), '/', '\\') : '';
            $class = $this->namespace.$namespace.'\\'.$file->getBasename('.php');
            $refl = new \ReflectionClass($class);

            $classRoute = $this->annotationReader->getClassAnnotation($refl, 'Symfony\Component\Routing\Annotation\Route');
            $routePrefix = $classRoute ? $classRoute->getName() : '';

            foreach ($refl->getMethods() as $method) {
                $annotations = $this->annotationReader->getMethodAnnotations($method);
                foreach ($annotations as $annotation) {
                    if (!$annotation instanceof MenuItem) {
                        continue;
                    }

                    if (null === $annotation->getPath()) {
                        $route = $this->annotationReader->getMethodAnnotation($method, 'Symfony\Component\Routing\Annotation\Route');
                        if (!$route) {
                            throw AnnotationException::semanticalError('An Symfony\Component\Routing\Annotation\Route annotation is required when using a App\Template\Annotation\MenuItem annotation');
                        }

                        $annotation->setPath($routePrefix.$route->getName());
                    }

                    if (!array_key_exists($annotation->menu, $this->menuItems)) {
                        $this->menuItems[$annotation->menu] = [];
                    }
                    $this->menuItems[$annotation->menu][] = $annotation;
                }
            }
        }
    }
}
