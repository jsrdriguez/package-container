<?php

use Package\Container\Container;
use \Package\Container\ContainerException;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function test_bind_from_closure()
    {
        $container = new Container();

        $container->bind('key', function() {
            return 'Object';
        });

        $this->assertSame('Object', $container->make('key'));
    }

    public function test_bind_instance()
    {
        $container = new Container();

        $stdClass = new StdClass();

        $container->instance('key', $stdClass);

        $this->assertSame($stdClass, $container->make('key'));
    }

    public function test_bind_from_class_name()
    {
        $container = new Container();

        $container->bind('key', 'StdClass');

        $this->assertInstanceOf('StdClass', $container->make('key'));
    }

    public function test_bind_with_automatic_resolution()
    {
        $container = new Container();

        $container->bind('foo', 'Foo');

        $this->assertInstanceOf('Foo', $container->make('foo'));
    }

    public function test_expected_container_exception_if_dependency_does_not_exist()
    {
        $this->expectException(ContainerException::class);// expectExceptionMessage Class Norn does not exist

        $container = new Container();

        $container->bind('qux', 'Qux');

        $container->make('qux');
    }

    public function test_class_does_not_exist()
    {
       $this->expectException(ReflectionException::class);

        $container = new Container();

        $container->bind('norf', 'Norf');

        $container->make("Norf");
    }

    public function test_container_make_with_arguments()
    {
        $container = new Container();

        $this->assertInstanceOf(
            MailDummy::class,
                $container->make('MailDummy', ['url' => 'hola.com', 'key'=> 'secret']));
    }

    public function test_container_make_with_default_arguments()
    {
        $container = new Container();

        $this->assertInstanceOf(
            MailDummy::class,
            $container->make('MailDummy', ['url' => 'hola.com']));
    }

    public function test_singleton_instance()
    {
        $container = new Container();

        $container->singleton('foo', 'Foo');

        $this->assertSame(
            $container->make('foo'),
            $container->make('foo'));
    }



}

class MailDummy {
    private $url;
    private $key;

    public function __construct($url, $key = 'secret')
    {
    }
}

class Foo {
    public function __construct(Bar $bar, Baz $baz)
    {
    }
}

class Bar {
    public function __construct(FooBar $fooBar)
    {
    }
}

class FooBar {
}

class Baz {
}

class Qux {
    public function __construct(Norn $norn)
    {
    }
}
