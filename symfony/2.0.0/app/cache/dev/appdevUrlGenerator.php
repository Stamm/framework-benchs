<?php

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


/**
 * appdevUrlGenerator
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appdevUrlGenerator extends Symfony\Component\Routing\Generator\UrlGenerator
{
    static private $declaredRouteNames = array(
       '_welcome' => true,
       '_demo_login' => true,
       '_security_check' => true,
       '_demo_logout' => true,
       'acme_demo_secured_hello' => true,
       '_demo_secured_hello' => true,
       '_demo_secured_hello_admin' => true,
       '_demo' => true,
       '_demo_hello' => true,
       '_demo_contact' => true,
       '_wdt' => true,
       '_profiler_search' => true,
       '_profiler_purge' => true,
       '_profiler_import' => true,
       '_profiler_export' => true,
       '_profiler_search_results' => true,
       '_profiler' => true,
       '_configurator_home' => true,
       '_configurator_step' => true,
       '_configurator_final' => true,
       'HelloBundle_homepage' => true,
       'hello' => true,
       'products' => true,
       'product' => true,
       'route_1' => true,
       'route_2' => true,
       'route_3' => true,
       'route_4' => true,
       'route_5' => true,
       'route_6' => true,
       'route_7' => true,
       'route_8' => true,
       'route_9' => true,
       'route_10' => true,
       'route_11' => true,
       'route_12' => true,
       'route_13' => true,
       'route_14' => true,
       'route_15' => true,
    );

    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function generate($name, $parameters = array(), $absolute = false)
    {
        if (!isset(self::$declaredRouteNames[$name])) {
            throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
        }

        $escapedName = str_replace('.', '__', $name);

        list($variables, $defaults, $requirements, $tokens) = $this->{'get'.$escapedName.'RouteInfo'}();

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);
    }

    private function get_welcomeRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\WelcomeController::indexAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/',  ),));
    }

    private function get_demo_loginRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::loginAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/demo/secured/login',  ),));
    }

    private function get_security_checkRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::securityCheckAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/demo/secured/login_check',  ),));
    }

    private function get_demo_logoutRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::logoutAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/demo/secured/logout',  ),));
    }

    private function getacme_demo_secured_helloRouteInfo()
    {
        return array(array (), array (  'name' => 'World',  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::helloAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/demo/secured/hello',  ),));
    }

    private function get_demo_secured_helloRouteInfo()
    {
        return array(array (  0 => 'name',), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::helloAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'name',  ),  1 =>   array (    0 => 'text',    1 => '/demo/secured/hello',  ),));
    }

    private function get_demo_secured_hello_adminRouteInfo()
    {
        return array(array (  0 => 'name',), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::helloadminAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'name',  ),  1 =>   array (    0 => 'text',    1 => '/demo/secured/hello/admin',  ),));
    }

    private function get_demoRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\DemoController::indexAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/demo/',  ),));
    }

    private function get_demo_helloRouteInfo()
    {
        return array(array (  0 => 'name',), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\DemoController::helloAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'name',  ),  1 =>   array (    0 => 'text',    1 => '/demo/hello',  ),));
    }

    private function get_demo_contactRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Acme\\DemoBundle\\Controller\\DemoController::contactAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/demo/contact',  ),));
    }

    private function get_wdtRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::toolbarAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'token',  ),  1 =>   array (    0 => 'text',    1 => '/_wdt',  ),));
    }

    private function get_profiler_searchRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::searchAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_profiler/search',  ),));
    }

    private function get_profiler_purgeRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::purgeAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_profiler/purge',  ),));
    }

    private function get_profiler_importRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::importAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_profiler/import',  ),));
    }

    private function get_profiler_exportRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::exportAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '.txt',  ),  1 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/\\.]+?',    3 => 'token',  ),  2 =>   array (    0 => 'text',    1 => '/_profiler/export',  ),));
    }

    private function get_profiler_search_resultsRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::searchResultsAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/search/results',  ),  1 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'token',  ),  2 =>   array (    0 => 'text',    1 => '/_profiler',  ),));
    }

    private function get_profilerRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::panelAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'token',  ),  1 =>   array (    0 => 'text',    1 => '/_profiler',  ),));
    }

    private function get_configurator_homeRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::checkAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_configurator/',  ),));
    }

    private function get_configurator_stepRouteInfo()
    {
        return array(array (  0 => 'index',), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::stepAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'index',  ),  1 =>   array (    0 => 'text',    1 => '/_configurator/step',  ),));
    }

    private function get_configurator_finalRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::finalAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_configurator/final',  ),));
    }

    private function getHelloBundle_homepageRouteInfo()
    {
        return array(array (  0 => 'name',), array (  '_controller' => 'Partaz\\Bundle\\Controller\\DefaultController::indexAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'name',  ),  1 =>   array (    0 => 'text',    1 => '/hello',  ),));
    }

    private function gethelloRouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'index',), array (), array (  0 =>   array (    0 => 'text',    1 => '/hello/:name',  ),));
    }

    private function getproductsRouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'products',), array (), array (  0 =>   array (    0 => 'text',    1 => '/products',  ),));
    }

    private function getproductRouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'product',), array (), array (  0 =>   array (    0 => 'text',    1 => '/product/:id',  ),));
    }

    private function getroute_1RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_1',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route1/:slug',  ),));
    }

    private function getroute_2RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_2',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route2/:slug',  ),));
    }

    private function getroute_3RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_3',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route3/:slug',  ),));
    }

    private function getroute_4RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_4',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route4/:slug',  ),));
    }

    private function getroute_5RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_5',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route5/:slug',  ),));
    }

    private function getroute_6RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_6',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route6/:slug',  ),));
    }

    private function getroute_7RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_7',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route7/:slug',  ),));
    }

    private function getroute_8RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_8',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route8/:slug',  ),));
    }

    private function getroute_9RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_9',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route9/:slug',  ),));
    }

    private function getroute_10RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_10',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route10/:slug',  ),));
    }

    private function getroute_11RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_11',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route11/:slug',  ),));
    }

    private function getroute_12RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_12',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route12/:slug',  ),));
    }

    private function getroute_13RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_13',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route13/:slug',  ),));
    }

    private function getroute_14RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_14',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route14/:slug',  ),));
    }

    private function getroute_15RouteInfo()
    {
        return array(array (), array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_15',), array (), array (  0 =>   array (    0 => 'text',    1 => '/route15/:slug',  ),));
    }
}
