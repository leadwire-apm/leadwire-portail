<?php declare(strict_types=1);

namespace ATS\AdminBundle\Routing;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutesLoader extends Loader
{
    private $managerRegistry;
    private $isLoaded;
    private $routesPrefix;
    private $scheme;
    private $host;

    public function __construct(
        ManagerRegistry $managerRegistry,
        $routesPrefix = '',
        $scheme = '',
        $host = ''
    ) {
        $this->isLoaded = false;
        $this->managerRegistry = $managerRegistry;
        $this->routesPrefix = $routesPrefix;
        $this->scheme = $scheme;
        $this->host = $host;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "admin" loader twice');
        }

        $routes = new RouteCollection();

        $metas = $this
            ->managerRegistry
            ->getManager()
            ->getMetadataFactory()
            ->getAllMetadata();

        foreach ($metas as $meta) {
            if ($meta->isMappedSuperclass == false) {
                $className = $meta->getName();
                $routeName = $this->routesPrefix . '_list_' . strtolower(str_replace('\\', '_', $className));
                $routes->add($routeName, $this->buildListRoute($className));
                $routeName = $this->routesPrefix . '_edit_' . strtolower(str_replace('\\', '_', $className));
                $routes->add($routeName, $this->buildEditRoute($className));
                $routeName = $this->routesPrefix . '_new_' . strtolower(str_replace('\\', '_', $className));
                $routes->add($routeName, $this->buildCreateRoute($className));
                $routeName = $this->routesPrefix . '_delete_' . strtolower(str_replace('\\', '_', $className));
                $routes->add($routeName, $this->buildDeleteRoute($className));
            }
        }

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'admin' === $type;
    }

    private function buildListRoute($entity)
    {
        $entity = str_replace('\\', '/', $entity);
        $path = sprintf("/%s/list/%s/page/{pageNumber}", $this->routesPrefix, strtolower($entity));

        $defaults = array(
            '_controller' => 'AdminBundle:Admin:listEntities',
            'pageNumber' => 1,
        );

        $options = [];
        $requirements = [];
        $schemes = [$this->scheme];
        $methods = ['GET'];
        $condition = '';

        return new Route($path, $defaults, $requirements, $options, $this->host, $schemes, $methods, $condition);
    }

    private function buildEditRoute($entity)
    {
        $entity = str_replace('\\', '/', $entity);
        $path = sprintf("/%s/edit/%s/{id}", $this->routesPrefix, strtolower($entity));

        $defaults = array(
            '_controller' => 'AdminBundle:Admin:editEntity',
        );

        $options = [];
        $requirements = [];
        $schemes = [$this->scheme];
        $methods = ['GET', 'POST'];
        $condition = '';

        return new Route($path, $defaults, $requirements, $options, $this->host, $schemes, $methods, $condition);
    }

    private function buildCreateRoute($entity)
    {
        $entity = str_replace('\\', '/', $entity);
        $path = sprintf("/%s/new/%s/{id}", $this->routesPrefix, strtolower($entity));

        $defaults = array(
            '_controller' => 'AdminBundle:Admin:createEntity',
            'id' => null,
        );

        $options = [];
        $requirements = [];
        $schemes = [$this->scheme];
        $methods = ['GET', 'POST'];
        $condition = '';

        return new Route($path, $defaults, $requirements, $options, $this->host, $schemes, $methods, $condition);
    }

    private function buildDeleteRoute($entity)
    {
        $entity = str_replace('\\', '/', $entity);
        $path = sprintf("/%s/delete/%s/{id}", $this->routesPrefix, strtolower($entity));

        $defaults = array(
            '_controller' => 'AdminBundle:Admin:deleteEntity',
        );

        $options = [];
        $requirements = [];
        $schemes = [$this->scheme];
        $methods = ['GET'];
        $condition = '';

        return new Route($path, $defaults, $requirements, $options, $this->host, $schemes, $methods, $condition);
    }
}
