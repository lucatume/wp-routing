<?php

use tad\interfaces\GlobalsAdapter;
use tad\adapters\Globals;
use tad\wrappers\Illuminate\Http\Request;
use \Illuminate\Http\Request as IRequest;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->sut = new Request();
        Request::_setInstance(null);
        Request::_setGlobals(null);
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
    public function testItShouldAccessTheWpGlobalVariableWhenCallingTheQueryMethod()
    {
        $className = '\tad\interfaces\GlobalsAdapters';
        $glad = $this->getMock($className, array('wp'));
        $glad->expects($this->any())
            ->method('wp')
            ->with('query_vars')
            ->will($this->returnValue(array('name' => 'John')));
        Request::_setGlobals($glad);
        $this->assertInternalType('array', Request::query());        
        $this->assertEquals('John', Request::query('name'));
        $this->assertNull(Request::query('some'));
        $this->assertEquals('foo', Request::query('some', 'foo'));
    }
}