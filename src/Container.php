<?php
namespace Package\Container;

use http\Exception\InvalidArgumentException;

class Container
{
    protected $bindings;
    protected $shared;
    private static $instance;

    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new Container;
        }

        return static::$instance;
    }

    public static function setInstance(Container $container)
    {
        static::$instance = $container;
    }

    public function bind($name, $resolver, $shared = false)
    {
        $this->bindings[$name] = [
            'resolver' => $resolver,
            'shared' => $shared
        ];
    }

    public function make($name, array $args = [])
    {
        if (isset($this->shared[$name]))
        {
            return $this->shared[$name];
        }

        if (isset($this->bindings[$name]['resolver'])) {
            $resolver = $this->bindings[$name]['resolver'];
            $shared = $this->bindings[$name]['shared'];
        } else {
            $resolver = $name;
            $shared = false;
        }

        if ($resolver instanceof \Closure) {
            $object = $resolver($this);
        } else {
            $object = $this->build($resolver, $args);
        }

        if ($shared) {
            $this->shared[$name] = $object;
        }

        return $object;
    }

    public function instance($name, $obj)
    {
        $this->shared[$name] = $obj;
    }

    public function singleton($name, $resolver)
    {
        $this->bind($name, $resolver,true);
    }

    public function build($name, $args = [])
    {
        $reflection = new \ReflectionClass($name);

        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("$name is not instanciable");
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $name;
        }

        $constructorParameters = $constructor->getParameters();

        $dependencies = [];
        foreach ($constructorParameters as $constructorParameter) {

            $parameterName = $constructorParameter->getName();

            if (isset($args[$parameterName])) {
                $dependencies[] = $args[$parameterName];
                continue;
            } else {

            }

            try {
                $parameterClass = $constructorParameter->getClass();
            } catch (\ReflectionException $e) {
                throw new ContainerException("Unable to build [$name]: " . $e->getMessage(), null, $e);
            }

            if ( $parameterClass != null) {
                $parameterClassName = $parameterClass->getName();
                $dependencies[] = $this->build($parameterClassName);
            } else {
                if (!$constructorParameter->isDefaultValueAvailable()) {
                    throw new ContainerException("Please provide the value of the parameter [$parameterName]: ");
                }

            }
        }

        return $reflection->newInstanceArgs($dependencies);

    }


}