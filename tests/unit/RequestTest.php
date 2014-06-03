<?php

use tad\wrappers\Illuminate\Http\Request;
use \Illuminate\Http\Request as IRequest;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sut = new Request();
        Request::_setInstance(null);
    }
    public function testItShouldBeInstantiatable()
    {
        $this->assertInstanceOf('\tad\wrappers\Illuminate\Http\Request', $this->sut);
    }
    public function testItWillCallInstanceMethodsEachTimeAStaticMethodIsCalled()
    {
        $className = '\Illuminate\Http\Request';
        $instance = $this->getMock($className, array('some'));
        $instance->expects($this->once())
            ->method('some')
            ->with('arg');
        Request::_setInstance($instance);
        Request::some('arg');
    }
}