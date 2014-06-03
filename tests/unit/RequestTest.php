<?php

use tad\wrappers\Illuminate\Http\Request;
use \Illuminate\Http\Request as IRequest;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sut = new Request();
    }

    protected function tearDown()
    {
    }

    public function testItShouldBeInstantiatable()
    {
        $this->assertInstanceOf('\tad\wrappers\Illuminate\Http\Request', $this->sut);
    }

    public function testItShouldAllowAccessingAnyIlluminateHttpRequestMethod()
    {
        foreach (get_class_methods('\Illuminate\Http\Request') as $method) {
                $this->assertTrue(method_exists($this->sut, $method));
            }    
    }
    public function testItShouldAllowInitializingAnInstanceUsingInitStaticMethod()
    {
        $this->assertInstanceOf('\Illuminate\Http\Request', Request::init());
    }
}