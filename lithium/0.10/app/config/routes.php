<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * The routes file is where you define your URL structure, which is an important part of the
 * [information architecture](http://en.wikipedia.org/wiki/Information_architecture) of your
 * application. Here, you can use _routes_ to match up URL pattern strings to a set of parameters,
 * usually including a controller and action to dispatch matching requests to. For more information,
 * see the `Router` and `Route` classes.
 *
 * @see lithium\net\http\Router
 * @see lithium\net\http\Route
 */
use lithium\net\http\Router;
use lithium\core\Environment;

/**
 * Here, we are connecting `'/'` (the base path) to controller called `'Pages'`,
 * its action called `view()`, and we pass a param to select the view file
 * to use (in this case, `/views/pages/home.html.php`; see `app\controllers\PagesController`
 * for details).
 *
 * @see app\controllers\PagesController
 */

Router::connect('/hello/{:name}', array('controller' => 'hello_world', 'action' => 'index'));
Router::connect('/products', array('controller' => 'products', 'action' => 'products'));
Router::connect('/product/{:id}', array('controller' => 'products', 'action' => 'product'));
Router::connect('/route_1/{:slug}', array('controller' => 'products', 'action' => 'route_1'));
Router::connect('/route_2/{:slug}', array('controller' => 'products', 'action' => 'route_2'));
Router::connect('/route_3/{:slug}', array('controller' => 'products', 'action' => 'route_3'));
Router::connect('/route_4/{:slug}', array('controller' => 'products', 'action' => 'route_4'));
Router::connect('/route_5/{:slug}', array('controller' => 'products', 'action' => 'route_5'));
Router::connect('/route_6/{:slug}', array('controller' => 'products', 'action' => 'route_6'));
Router::connect('/route_7/{:slug}', array('controller' => 'products', 'action' => 'route_7'));
Router::connect('/route_8/{:slug}', array('controller' => 'products', 'action' => 'route_8'));
Router::connect('/route_9/{:slug}', array('controller' => 'products', 'action' => 'route_9'));
Router::connect('/route_10/{:slug}', array('controller' => 'products', 'action' => 'route_10'));
Router::connect('/route_11/{:slug}', array('controller' => 'products', 'action' => 'route_11'));
Router::connect('/route_12/{:slug}', array('controller' => 'products', 'action' => 'route_12'));
Router::connect('/route_13/{:slug}', array('controller' => 'products', 'action' => 'route_13'));
Router::connect('/route_14/{:slug}', array('controller' => 'products', 'action' => 'route_14'));
Router::connect('/route_15/{:slug}', array('controller' => 'products', 'action' => 'route_15'));

/**
 * Connect the rest of `PagesController`'s URLs. This will route URLs like `/pages/about` to
 * `PagesController`, rendering `/views/pages/about.html.php` as a static page.
 */
Router::connect('/pages/{:args}', 'Pages::view');

/**
 * Add the testing routes. These routes are only connected in non-production environments, and allow
 * browser-based access to the test suite for running unit and integration tests for the Lithium
 * core, as well as your own application and any other loaded plugins or frameworks. Browse to
 * [http://path/to/app/test](/test) to run tests.
 */
if (!Environment::is('production')) {
	Router::connect('/test/{:args}', array('controller' => 'lithium\test\Controller'));
	Router::connect('/test', array('controller' => 'lithium\test\Controller'));
}

/**
 * ### Database object routes
 *
 * The routes below are used primarily for accessing database objects, where `{:id}` corresponds to
 * the primary key of the database object, and can be accessed in the controller as
 * `$this->request->id`.
 *
 * If you're using a relational database, such as MySQL, SQLite or Postgres, where the primary key
 * is an integer, uncomment the routes below to enable URLs like `/posts/edit/1138`,
 * `/posts/view/1138.json`, etc.
 */
// Router::connect('/{:controller}/{:action}/{:id:\d+}.{:type}', array('id' => null));
// Router::connect('/{:controller}/{:action}/{:id:\d+}');

/**
 * If you're using a document-oriented database, such as CouchDB or MongoDB, or another type of
 * database which uses 24-character hexidecimal values as primary keys, uncomment the routes below.
 */
// Router::connect('/{:controller}/{:action}/{:id:[0-9a-f]{24}}.{:type}', array('id' => null));
// Router::connect('/{:controller}/{:action}/{:id:[0-9a-f]{24}}');

/**
 * Finally, connect the default route. This route acts as a catch-all, intercepting requests in the
 * following forms:
 *
 * - `/foo/bar`: Routes to `FooController::bar()` with no parameters passed.
 * - `/foo/bar/param1/param2`: Routes to `FooController::bar('param1, 'param2')`.
 * - `/foo`: Routes to `FooController::index()`, since `'index'` is assumed to be the action if none
 *   is otherwise specified.
 *
 * In almost all cases, custom routes should be added above this one, since route-matching works in
 * a top-down fashion.
 */
Router::connect('/{:controller}/{:action}/{:args}');

?>