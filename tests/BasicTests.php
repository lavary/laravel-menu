<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class BasicTests extends TestCase
{
    private $facadeMocks = array();
    public function setUp()
    {
        parent::setUp();
        
        $app = m::mock('app')->shouldReceive('instance')->getMock();
        
        $this->facadeMocks['config'] = m::mock('config');
        Config::setFacadeApplication($app);
        Config::swap($this->facadeMocks['config']);
        
        $this->facadeMocks['url'] = m::mock('url');
        URL::setFacadeApplication($app);
        URL::swap($this->facadeMocks['url']);
        
        $this->facadeMocks['request'] = m::mock('request');
        Request::setFacadeApplication($app);
        Request::swap($this->facadeMocks['request']);
    }
    
    public function tearDown()
    {
        m::close();
    }
    
    public function testTest()
    {
        $this->assertTrue(true);
    }
    
    public function testBasic()
    {
        $config = [
            'default' => [
                'auto_activate' => true,
                'activate_parents' => true,
                'active_class' => 'active',
                'restful' => false,
                'cascade_data' => true,
                'rest_base' => '',      // string|array
                'active_element' => 'item',  // item|link
            ],
        ];
        
        Config::shouldReceive('get')->once()->with('laravel-menu.settings')
            ->andReturn($config);
        
        URL::shouldReceive('to')->times(7)->withArgs(['/', [], null])
            ->andReturn('http://test.com');
        URL::shouldReceive('to')->times(7)->withArgs(['/about', [], null])
            ->andReturn('http://test.com/about');
        URL::shouldReceive('to')->times(7)->withArgs(['/services', [], null])
            ->andReturn('http://test.com/services');
        URL::shouldReceive('to')->times(7)->withArgs(['/contact', [], null])
            ->andReturn('http://test.com/contact');

        // To trigger "active" on the /about route.
        Request::shouldReceive('url')->times(4)
            ->andReturn('http://test.com/about');
        
        $menu = new \Lavary\Menu\Menu();
        $builder = new \Lavary\Menu\Builder(
            'basic',
            $menu->loadConf('basic')
        );
        
        $builder->add('Home');
        $builder->add('About', 'about');
        $builder->add('Services', 'services');
        $builder->add('Contact', 'contact');
        
        $this->assertEquals('<ul><li><a href="http://test.com">Home</a></li><li class="active"><a href="http://test.com/about">About</a></li><li><a href="http://test.com/services">Services</a></li><li><a href="http://test.com/contact">Contact</a></li></ul>', $builder->asUl());
        $this->assertEquals('<ol><li><a href="http://test.com">Home</a></li><li class="active"><a href="http://test.com/about">About</a></li><li><a href="http://test.com/services">Services</a></li><li><a href="http://test.com/contact">Contact</a></li></ol>', $builder->asOl());
        $this->assertEquals('<div><div><a href="http://test.com">Home</a></div><div class="active"><a href="http://test.com/about">About</a></div><div><a href="http://test.com/services">Services</a></div><div><a href="http://test.com/contact">Contact</a></div></div>', $builder->asDiv());
    }
}
