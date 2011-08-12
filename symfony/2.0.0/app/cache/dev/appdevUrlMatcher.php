<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appdevUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appdevUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = urldecode($pathinfo);

        // _welcome
        if (rtrim($pathinfo, '/') === '') {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', '_welcome');
            }
            return array (  '_controller' => 'Acme\\DemoBundle\\Controller\\WelcomeController::indexAction',  '_route' => '_welcome',);
        }

        // _demo_login
        if ($pathinfo === '/demo/secured/login') {
            return array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::loginAction',  '_route' => '_demo_login',);
        }

        // _security_check
        if ($pathinfo === '/demo/secured/login_check') {
            return array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::securityCheckAction',  '_route' => '_security_check',);
        }

        // _demo_logout
        if ($pathinfo === '/demo/secured/logout') {
            return array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::logoutAction',  '_route' => '_demo_logout',);
        }

        // acme_demo_secured_hello
        if ($pathinfo === '/demo/secured/hello') {
            return array (  'name' => 'World',  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::helloAction',  '_route' => 'acme_demo_secured_hello',);
        }

        // _demo_secured_hello
        if (0 === strpos($pathinfo, '/demo/secured/hello') && preg_match('#^/demo/secured/hello/(?P<name>[^/]+?)$#x', $pathinfo, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::helloAction',)), array('_route' => '_demo_secured_hello'));
        }

        // _demo_secured_hello_admin
        if (0 === strpos($pathinfo, '/demo/secured/hello/admin') && preg_match('#^/demo/secured/hello/admin/(?P<name>[^/]+?)$#x', $pathinfo, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Acme\\DemoBundle\\Controller\\SecuredController::helloadminAction',)), array('_route' => '_demo_secured_hello_admin'));
        }

        if (0 === strpos($pathinfo, '/demo')) {
            // _demo
            if (rtrim($pathinfo, '/') === '/demo') {
                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', '_demo');
                }
                return array (  '_controller' => 'Acme\\DemoBundle\\Controller\\DemoController::indexAction',  '_route' => '_demo',);
            }

            // _demo_hello
            if (0 === strpos($pathinfo, '/demo/hello') && preg_match('#^/demo/hello/(?P<name>[^/]+?)$#x', $pathinfo, $matches)) {
                return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Acme\\DemoBundle\\Controller\\DemoController::helloAction',)), array('_route' => '_demo_hello'));
            }

            // _demo_contact
            if ($pathinfo === '/demo/contact') {
                return array (  '_controller' => 'Acme\\DemoBundle\\Controller\\DemoController::contactAction',  '_route' => '_demo_contact',);
            }

        }

        // _wdt
        if (preg_match('#^/_wdt/(?P<token>[^/]+?)$#x', $pathinfo, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::toolbarAction',)), array('_route' => '_wdt'));
        }

        if (0 === strpos($pathinfo, '/_profiler')) {
            // _profiler_search
            if ($pathinfo === '/_profiler/search') {
                return array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::searchAction',  '_route' => '_profiler_search',);
            }

            // _profiler_purge
            if ($pathinfo === '/_profiler/purge') {
                return array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::purgeAction',  '_route' => '_profiler_purge',);
            }

            // _profiler_import
            if ($pathinfo === '/_profiler/import') {
                return array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::importAction',  '_route' => '_profiler_import',);
            }

            // _profiler_export
            if (0 === strpos($pathinfo, '/_profiler/export') && preg_match('#^/_profiler/export/(?P<token>[^/\\.]+?)\\.txt$#x', $pathinfo, $matches)) {
                return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::exportAction',)), array('_route' => '_profiler_export'));
            }

            // _profiler_search_results
            if (preg_match('#^/_profiler/(?P<token>[^/]+?)/search/results$#x', $pathinfo, $matches)) {
                return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::searchResultsAction',)), array('_route' => '_profiler_search_results'));
            }

            // _profiler
            if (preg_match('#^/_profiler/(?P<token>[^/]+?)$#x', $pathinfo, $matches)) {
                return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::panelAction',)), array('_route' => '_profiler'));
            }

        }

        if (0 === strpos($pathinfo, '/_configurator')) {
            // _configurator_home
            if (rtrim($pathinfo, '/') === '/_configurator') {
                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', '_configurator_home');
                }
                return array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::checkAction',  '_route' => '_configurator_home',);
            }

            // _configurator_step
            if (0 === strpos($pathinfo, '/_configurator/step') && preg_match('#^/_configurator/step/(?P<index>[^/]+?)$#x', $pathinfo, $matches)) {
                return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::stepAction',)), array('_route' => '_configurator_step'));
            }

            // _configurator_final
            if ($pathinfo === '/_configurator/final') {
                return array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::finalAction',  '_route' => '_configurator_final',);
            }

        }

        // HelloBundle_homepage
        if (0 === strpos($pathinfo, '/hello') && preg_match('#^/hello/(?P<name>[^/]+?)$#x', $pathinfo, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'Partaz\\Bundle\\Controller\\DefaultController::indexAction',)), array('_route' => 'HelloBundle_homepage'));
        }

        // hello
        if ($pathinfo === '/hello/:name') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'index',  '_route' => 'hello',);
        }

        // products
        if ($pathinfo === '/products') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'products',  '_route' => 'products',);
        }

        // product
        if ($pathinfo === '/product/:id') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'product',  '_route' => 'product',);
        }

        // route_1
        if ($pathinfo === '/route1/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_1',  '_route' => 'route_1',);
        }

        // route_2
        if ($pathinfo === '/route2/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_2',  '_route' => 'route_2',);
        }

        // route_3
        if ($pathinfo === '/route3/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_3',  '_route' => 'route_3',);
        }

        // route_4
        if ($pathinfo === '/route4/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_4',  '_route' => 'route_4',);
        }

        // route_5
        if ($pathinfo === '/route5/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_5',  '_route' => 'route_5',);
        }

        // route_6
        if ($pathinfo === '/route6/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_6',  '_route' => 'route_6',);
        }

        // route_7
        if ($pathinfo === '/route7/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_7',  '_route' => 'route_7',);
        }

        // route_8
        if ($pathinfo === '/route8/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_8',  '_route' => 'route_8',);
        }

        // route_9
        if ($pathinfo === '/route9/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_9',  '_route' => 'route_9',);
        }

        // route_10
        if ($pathinfo === '/route10/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_10',  '_route' => 'route_10',);
        }

        // route_11
        if ($pathinfo === '/route11/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_11',  '_route' => 'route_11',);
        }

        // route_12
        if ($pathinfo === '/route12/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_12',  '_route' => 'route_12',);
        }

        // route_13
        if ($pathinfo === '/route13/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_13',  '_route' => 'route_13',);
        }

        // route_14
        if ($pathinfo === '/route14/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_14',  '_route' => 'route_14',);
        }

        // route_15
        if ($pathinfo === '/route15/:slug') {
            return array (  '_bundle' => 'HelloBundle',  '_controller' => 'Hello',  '_action' => 'route_15',  '_route' => 'route_15',);
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
