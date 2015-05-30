RapidRoute - Another fast router for PHP
========================================
[![Build status](https://img.shields.io/travis/TimeToogo/RapidRoute/master.svg?style=flat-square)](https://travis-ci.org/TimeToogo/RapidRoute)
[![Code quality](https://img.shields.io/scrutinizer/g/TimeToogo/RapidRoute.svg?style=flat-square)](https://scrutinizer-ci.com/g/TimeToogo/RapidRoute)
[![Coverage Status](https://img.shields.io/coveralls/TimeToogo/RapidRoute/master.svg?style=flat-square)](https://coveralls.io/r/TimeToogo/RapidRoute?branch=master)
[![Stable Release](https://img.shields.io/packagist/vpre/timetoogo/rapid-route.svg?style=flat-square)](https://packagist.org/packages/timetoogo/rapid-route)
[![License](https://img.shields.io/packagist/l/timetoogo/rapid-route.svg?style=flat-square)](https://packagist.org/packages/timetoogo/rapid-route)

RapidRoute aims to be another fast router for PHP. This library 
takes a different approach to uri routing by compiling the router to optimized
PHP code, minimizing the need for traditional regular expressions.

As this project focuses on performance, the scope of this library is limited.
All in all, this library provides the ability to match a supplied HTTP request 
(method and uri) against a set of route definitions. See below for usage examples.

Benchmarks
==========

| Test Name                     | RapidRoute (req/sec) | FastRoute (req/sec) | Difference | Change        |
| ----------------------------- | -------------------- | ------------------- | ---------- | ------------- |
| First static route            | 3385.28              | 2906.64             | +478.64    | 16.47% faster |
| Last static route             | 3419.56              | 2901.09             | +518.47    | 17.87% faster |
| First dynamic route           | 3428.94              | 2829.18             | +599.76    | 21.20% faster |
| Last dynamic route            | 3379.56              | 2890.18             | +489.38    | 16.93% faster |
| Non-existent route            | 3412.31              | 2823.27             | +589.04    | 20.86% faster |
| Longest route                 | 3371.36              | 2853.40             | +517.96    | 18.15% faster |
| Invalid method, static route  | 3125.81              | 2864.19             | +261.62    | 9.13% faster  |
| Invalid method, dynamic route | 3402.57              | 2847.55             | +555.02    | 19.49% faster |

These results are generated using [this benchmark suite][bench] running on PHP 5.5 with opcache enabled.
These results indicate a consistent 10-20% performance gain over FastRoute depending on the input uri and http method.

[bench]: https://github.com/TimeToogo/RouterBenchmark

Installation
============

This project is compatible with PHP 5.4+, there has been no tagged releases as of yet.
It can be loaded via composer:

```
composer require timetoogo/rapid-route dev-master
```

Router Usage
============

This library is designed to be used by another library/framework or as a standalone package.
It provides specific APIs for each use case.

Usage in a framework
====================

A framework often provides its own wrapper API so this library offers a lower-level API in
this case. A basic example is shown:

```php
use RapidRoute\CompiledRouter;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

$compiledRouterPath = __DIR__ . '/path/to/compiled/router.php';

$router = CompiledRouter::generate(
    $compiledRouterPath,
    function (RouteCollection $routes) {
        // Route definitions...
    }
);

$result = $router($httpMethod, $uri);

switch ($result[0]) {
    case MatchResult::NOT_FOUND:
        // 404 Not Found...
        break;

    case MatchResult::HTTP_METHOD_NOT_ALLOWED:
        // 405 Method Not Allowed...
        $allowedMethods = $result[1];
        break;

    case MatchResult::FOUND:
        // Matched route, dispatch to associated handler...
        $routeData = $result[1];
        $parameters = $result[2];
        break;
}
```

The result from the router is an array that contains the result status as the first element.
The following elements of the array are dependent on the status and will be one of three formats:

```php
// Could not match route
[MatchResult::NOT_FOUND]

// Matched route but disallowed HTTP method
[MatchResult::HTTP_METHOD_NOT_ALLOWED, [<allowed HTTP methods>]]

// Found matching route
[MatchResult::FOUND, <associated route data>, [<matched route parameters>]]
```

Usage as a standalone package
============================

If this library is intended to be used as a standalone package, a cleaner and more extensive
wrapper API is provided. A similar example showing off this the API is shown:

```php
use RapidRoute\Router;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

$compiledRouterPath = __DIR__ . '/path/to/compiled/router.php';

$router = new Router(
    $compiledRouterPath,
    function (RouteCollection $routes) {
        // Route definitions...
    }
);

// If true the router will be recompiled every request
$router->setDevelopmentMode($developmentMode);
// Or you can manually call when appropriate
// $router->clearCompiled();

$result = $router->match($httpMethod, $uri);

if($result->isNotFound()) {
    // 404 Not Found...
} elseif ($result->isDisallowedHttpMethod()) {
    // 405 Method Not Allowed...
    $allowedMethods = $result->getAllowedHttpMethods();
} elseif ($result->isFound()) {
    // Matched route, dispatch to associated handler...
    $routeData = $result->getRouteData();
    $parameters = $result->getParameters();
}

// Or if preferred
switch ($result->getStatus()) {
    // case MatchResult::* as above
}
```

The result from the call to `$router->match(...)` will be an instance of `RapidRoute\MatchResult`.

Route definitions
=================

**Route patterns**

To define the routes, a familiar url structure is used: 

```php
// This is a static route, it will extactly match '/shop/product'
'/shop/product'

// A dynamic route can be defined using the {...} parameter syntax
// This will match urls such as '/shop/product/123' or '/shop/product/abcd'
'/shop/product/{id}'

// If a route parameter must match a specific format you can define it
// by passing an array with a regex in the following format
['/shop/product/{id}', 'id' => '\d+']

// Or, if you prefer, you can use the predefined patterns using RapidRoute\Pattern
['/shop/product/{id}', 'id' => Pattern::DIGITS]

// More complex routes patterns are supported
[
  '/shop/category/{category_id}/product/search/{filter_by}:{filter_value}',
  'category_id' => Pattern::DIGITS,
  'filter_by'   => Pattern::ALPHA_LOWER
]
```

**Adding Routes**

To define the routes, the router API takes a `callable` parameter which will
be called with an instance of `RapidRoute\RouteCollection` when the router is
being compiled. This can be used like so:

```php
function (RouteCollection $routes) {
    $routes->add('GET', '/', ['name' => 'home']);
    
    // There are also shortcuts for the standard HTTP methods
    // the following is equivalent to the previous call
    $routes->get('/', ['name' => 'home']);
    
    // Or if any HTTP method should be allowed:
    $routes->any('/contact', ['name' => 'contact']);
}
```

Using the `RouteCollection` you can also define a route parameter regex globally 
to avoid repetitions:

```php
function (RouteCollection $routes) {
    $routes->param('product_id', Pattern::DIGITS);
    $routes->param('page_slug', Pattern::ALPHA_NUM_DASH);
    
    $routes->get('/shop/product/{product_id}', ['name' => 'shop.product.show']);
    $routes->get('/page/{page_slug}', ['name' => 'page.show']);
}
```

Basic usage example
===================

The associated route data will be available when the route is matched. This is a very
basic example of how this library may be implemented as a standalone router package.
The route data contains the associated handler so it can be easily dispatched
when the route is matched.

```php
use RapidRoute\Router;
use RapidRoute\RouteCollection;
use RapidRoute\Pattern;
use RapidRoute\MatchResult;

require __DIR__ . './vendor/autoload.php';

$compiledRouterPath = __DIR__ . '/path/to/compiled/router.php';

$router = new Router(
    $compiledRouterPath,
    function (RouteCollection $routes) {
        $routes->param('user_id', Pattern::DIGITS);

        $routes->get('/', ['handler' => ['HomeController', 'index']]);
        $routes->get('/user', ['handler' => ['UserController', 'index']);
        $routes->get('/user/create', ['handler' => ['UserController', 'create']);
        $routes->post('/user', ['handler' => ['UserController', 'store']);
        $routes->get('/user/{user_id}', ['handler' => ['UserController', 'show']);
        $routes->get('/user/{user_id}/edit', ['handler' => ['UserController', 'edit']);
        $routes->add(['PUT', 'PATCH'], '/user/{user_id}', ['handler' => ['UserController', 'update']);
        $routes->delete('/user/{user_id}', ['handler' => ['UserController', 'delete']);
    }
);

$router->setDevelopmentMode($developmentMode);

$result = $router->match($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

switch($result->getStatus()) {
    case MatchResult::NOT_FOUND:
        render((new ErrorController())->notFound());
        break;
    
    case MatchResult::HTTP_METHOD_NOT_ALLOWED:
        render((new ErrorController())->methodNotAllowed($result->getAllowedHttpMethods()));
        break;
    
    case MatchResult::FOUND:
        // Dispatcher matched route to associated handler
        list($controller, $method) = $result->getRouteData()['handler'];
        $parameters = $result->getParameters();
        
        render((new $controller())->{$method}($parameters));
        break;
}

```

Here are some examples of how this set up should handle the incoming request:

| Request        | Dispatched Handler                             | 
|----------------|------------------------------------------------| 
| GET  /         | `HomeController::index([])`                    | 
| GET  /user     | `UserController::index([])`                    | 
| POST /user     | `UserController::store([])`                    | 
| POST /         | `ErrorController::methodNotAllowed(['GET'])`   | 
| GET  /abc      | `ErrorController::notFound()`                  | 
| GET  /user/123 | `UserController::show(['user_id' => '123'])`   | 
| PUT  /user/123 | `UserController::update(['user_id' => '123'])` | 
| PUT  /user/abc | `ErrorController::notFound()`                  | 

Notes
=====
 - When matching a uri, the uri string **must** contain a preceding `/` if it is not empty.
 - Route defined with a trailing slash will **not** match a uri without the slash
    - `'/shop/product/'` will not match `'/shop/product'` and vice-versa
 - A route that allows the `GET` method will also accept the `HEAD` method as per HTTP spec.

Compilation
===========

Given that this library route definitions compiles to plain PHP, there are much
room for optimization. The current approach is using a tree structure matching
by each segment in a uri (`'/shop/product'` is composed of the `'shop'` and `'product'` segments).
Currently the structure is compiled to nested `switch` and `if` blocks using
optimized comparisons where applicable.

One consideration of the compiled router is that it must be able to be called
directly and as such must handle the any expected error cases within the compiled
router.

**Example compiled router**

Route definitions:

```php
$router = CompiledRouter::generate(
    __DIR__ . '/compiled/rr.php',
    function (\RapidRoute\RouteCollection $routes) {
        $routes->param('post_slug', Pattern::APLHA_NUM_DASH);

        $routes->get('/', ['name' => 'home']);
        $routes->get('/blog', ['name' => 'blog.index']);
        $routes->get('/blog/post/{post_slug}', ['name' => 'blog.post.show']);
        $routes->post('/blog/post/{post_slug}/comment', ['name' => 'blog.post.comment']);
    }
)
```

Currently the compiled router for the above will be similar to the following:

```php
use RapidRoute\RapidRouteException;

return function ($method, $uri) {
    if($uri === '') {
        return [0];
    } elseif ($uri[0] !== '/') {
        throw new RapidRouteException("Cannot match route: non-empty uri must be prefixed with '/', '{$uri}' given");
    }

    $segments = explode('/', substr($uri, 1));

    switch (count($segments)) {
        case 1:
            list($s0) = $segments;
            if ($s0 === '') {
                switch ($method) {
                    case 'GET':
                    case 'HEAD':
                        return [2, ['name' => 'home'], []];
                    default:
                        $allowedHttpMethods[] = 'GET';
                        $allowedHttpMethods[] = 'HEAD';
                        break;
                }
            }
            if ($s0 === 'blog') {
                switch ($method) {
                    case 'GET':
                    case 'HEAD':
                        return [2, ['name' => 'blog.index'], []];
                    default:
                        $allowedHttpMethods[] = 'GET';
                        $allowedHttpMethods[] = 'HEAD';
                        break;
                }
            }
            return isset($allowedHttpMethods) ? [1, $allowedHttpMethods] : [0];
            break;
        
        case 3:
            list($s0, $s1, $s2) = $segments;
            if ($s0 === 'blog' && $s1 === 'post' && ctype_alnum(str_replace('-', '', $s2))) {
                switch ($method) {
                    case 'GET':
                    case 'HEAD':
                        return [2, ['name' => 'blog.post.show'], ['post_slug' => $s2]];
                    default:
                        $allowedHttpMethods[] = 'GET';
                        $allowedHttpMethods[] = 'HEAD';
                        break;
                }
            }
            return isset($allowedHttpMethods) ? [1, $allowedHttpMethods] : [0];
            break;
        
        case 4:
            list($s0, $s1, $s2, $s3) = $segments;
            if ($s0 === 'blog' && $s1 === 'post' && $s3 === 'comment' && ctype_alnum(str_replace('-', '', $s2))) {
                switch ($method) {
                    case 'POST':
                        return [2, ['name' => 'blog.post.comment'], ['post_slug' => $s2]];
                    default:
                        $allowedHttpMethods[] = 'POST';
                        break;
                }
            }
            return isset($allowedHttpMethods) ? [1, $allowedHttpMethods] : [0];
            break;
        
        default:
            return [0];
    }
};
```

The complexity of the router will grow in proportion to the number and complexity of the route definitions.

