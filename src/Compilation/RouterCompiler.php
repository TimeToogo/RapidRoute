<?php

namespace RapidRoute\Compilation;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\RouteTree\ChildrenNodeCollection;
use RapidRoute\Compilation\RouteTree\MatchedRouteData;
use RapidRoute\Compilation\RouteTree\MatchedRouteDataMap;
use RapidRoute\Compilation\RouteTree\RouteTree;
use RapidRoute\Compilation\RouteTree\RouteTreeBuilder;
use RapidRoute\Compilation\RouteTree\RouteTreeOptimizer;
use RapidRoute\Route;
use RapidRoute\RouteCollection;
use RapidRoute\RouteSegments\StaticSegment;

/**
 * The default router compiler class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouterCompiler
{
    const COMPILED_ROUTER_TEMPLATE = <<<'PHP'
<?php

use RapidRoute\RapidRouteException;
use RapidRoute\MatchResult as Result;

return function ($method, $uri) {
    if ($uri !== '' && $uri[0] !== '/') {
        throw new RapidRouteException("Cannot match route: non-empty uri must be prefixed with '/', '{$uri}' given");
    }

    static $staticRoutes;
    if(!isset($staticRoutes)) {
        $staticRoutes =
{static_route_map};
    }

    $staticAllowedMethods = [];
    $staticRouteMatch =& $staticRoutes[$uri];
    if(isset($staticRouteMatch)) {
        if(isset($staticRouteMatch[$method])) {
            return Result::found($staticRouteMatch[$method], []);
        } elseif (isset($staticRouteMatch['*'])) {
            return Result::found($staticRouteMatch['*'], []);
        } else {
            $staticAllowedMethods = array_keys($staticRouteMatch);
        }
    }

    $segments = explode('/', substr($uri, 1));

{body}
};

PHP;

    /**
     * @var RouteTreeBuilder
     */
    protected $treeBuilder;

    /**
     * @var RouteTreeOptimizer
     */
    protected $treeOptimizer;

    public function __construct(RouteTreeBuilder $treeBuilder = null, RouteTreeOptimizer $treeOptimizer = null)
    {
        $this->treeBuilder   = $treeBuilder ?: new RouteTreeBuilder();
        $this->treeOptimizer = $treeOptimizer ?: new RouteTreeOptimizer();
    }

    /**
     * Compiles a the supplied route collection to plain PHP
     * that can be cached in a file and will return a closure
     * to perform the routing.
     *
     * @param RouteCollection $routes
     *
     * @return string
     */
    public function compileRoutesToPhpClosure(RouteCollection $routes)
    {
        $staticRoutes = [];
        $dynamicRoutes = [];
        $dynamicRoutesGroups = [];
        $anyMethodDynamicRoutes = [];

        foreach($routes->asArray() as $route) {
            if($route->isStatic()) {
                $staticRoutes[] = $route;
            } else {
                if($route->allowsAnyHttpMethod()) {
                    $anyMethodDynamicRoutes[] = $route;
                } else {
                    foreach($route->getHttpMethods() as $method) {
                        $dynamicRoutesGroups[$method][] = $route;
                    }
                    $dynamicRoutes[] = $route;
                }
            }
        }

        $staticRouteCode = new PhpBuilder();
        $staticRouteCode->indent = 2;
        $this->compileStaticRouteMap($staticRouteCode, $staticRoutes);

        $code         = new PhpBuilder();
        $code->indent = 1;

        $code->appendLine('switch ($method) {');
        $code->indent++;

        $this->gotoFallback = 'anyMethodFallback';
        $this->invalidMethod = false;
        $this->isDefault = false;

        foreach($dynamicRoutesGroups as $method => $methodRoutes) {
            $code->appendLine('case ' . $this->export($method) . ':');
            $code->indent++;

            $this->currentHttpMethod = $method;
            $routeTree = $this->treeBuilder->build($methodRoutes);
            $routeTree = $this->treeOptimizer->optimize($routeTree);
            $this->compileRouteTree($code, $routeTree);

            $code->appendLine('break;');
            $code->indent--;
        }

        $code->indent--;
        $code->appendLine('}');

        $this->gotoFallback = 'invalidMethodFallback';
        $this->isDefault = true;

        $code->appendLine('anyMethodFallback:');

        $routeTree = $this->treeBuilder->build($anyMethodDynamicRoutes);
        $routeTree = $this->treeOptimizer->optimize($routeTree);
        $this->compileRouteTree($code, $routeTree);

        $this->gotoFallback = null;
        $this->invalidMethod = true;
        $this->isDefault = false;

        $code->appendLine('invalidMethodFallback:');
        $routeTree = $this->treeBuilder->build($dynamicRoutes);
        $routeTree = $this->treeOptimizer->optimize($routeTree);
        $this->compileRouteTree($code, $routeTree);

        return $this->formatPhpRouterTemplate(['{static_route_map}' => $staticRouteCode->getCode(), '{body}' => $code->getCode()]);
    }

    protected function formatPhpRouterTemplate(array $replacements)
    {
        return strtr(self::COMPILED_ROUTER_TEMPLATE, $replacements);
    }

    protected function compileStaticRouteMap(PhpBuilder $code, array $staticRoutes)
    {
        $routeMap = [];

        /** @var Route[] $staticRoutes */
        foreach($staticRoutes as $staticRoute) {
            $route = '';

            /** @var StaticSegment $segment */
            foreach($staticRoute->getSegments() as $segment) {
                $route .= '/' . $segment->getValue();
            }

            if($staticRoute->allowsAnyHttpMethod()) {
                $routeMap[$route]['*'] = $staticRoute->getData();
            } else {
                foreach($staticRoute->getHttpMethods() as $method) {
                    $routeMap[$route][$method] = $staticRoute->getData();
                }
            }
        }

        $code->append($this->export($routeMap));
    }

    protected function compileRouteTree(PhpBuilder $code, RouteTree $routeTree)
    {
        $code->appendLine('switch (count($segments)) {');
        $code->indent++;

        foreach ($routeTree->getSegmentDepthNodesMap() as $segmentDepth => $nodes) {
            $code->appendLine('case ' . $this->export($segmentDepth) . ':');
            $code->indent++;

            $segmentVariables = [];
            for($i = 0; $i < $segmentDepth; $i++) {
                // Use
                $segmentVariables[$i] = '$s' . $i;
            }

            $code->appendLine('list(' . implode(', ', $segmentVariables) . ') = $segments;');
            $this->compileSegmentNodes($code, $nodes, $segmentVariables);

            $code->appendLine('break;');
            $code->indent--;
            $code->appendLine();
        }

        $code->appendLine('default:');
        $code->indent++;
        $this->compileNotFound($code);
        $code->indent--;

        $code->indent--;
        $code->append('}');
    }

    protected function compileSegmentNodes(PhpBuilder $code, ChildrenNodeCollection $nodes, array $segmentVariables, $notFound = true, array $parameters = array())
    {
        $exclusiveCases = $nodes->areChildrenExclusive();
        $first = true;

        foreach ($nodes->getChildren() as $node) {
            /** @var SegmentMatcher[] $segmentMatchers */
            $segmentMatchers  = $node->getMatchers();

            $conditions       = [];

            $currentParameter = empty($parameters) ? 0 : max(array_keys($parameters)) + 1;
            $count = $currentParameter;
            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $conditions[] = $matcher->getConditionExpression($segmentVariables[$segmentDepth], $count++);
            }

            $conditional = ($first || !$exclusiveCases) ? 'if' : 'elseif';
            $code->appendLine($conditional . ' (' . implode(' && ', $conditions) . ') {');
            $code->indent++;

            $count = $currentParameter;
            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $matchedParameters = $matcher->getMatchedParameterExpressions($segmentVariables[$segmentDepth], $count++);

                foreach($matchedParameters as $parameterKey => $matchedParameter) {
                    $parameters[$parameterKey] = $matchedParameter;
                }
            }

            if ($node->isLeafNode()) {
                $this->compiledRouteHttpMethodMatch($code, $node->getContents(), $parameters);
            } else {
                $this->compileSegmentNodes($code, $node->getContents(), $segmentVariables, $exclusiveCases, $parameters);
            }

            $code->indent--;
            $code->appendLine('}');
            $first = false;
        }

        if($notFound) {
            if($exclusiveCases) {
                $code->appendLine('else {');
                $code->indent++;
            }

            $this->compileNotFound($code);

            if($exclusiveCases) {
                $code->indent--;
                $code->appendLine('}');
            }
        }
    }

    protected function compiledRouteHttpMethodMatch(PhpBuilder $code, MatchedRouteDataMap $routeDataMap, array $parameters)
    {
        if($this->invalidMethod) {
            $this->compileDisallowedHttpMethod($code, $routeDataMap->getAllowedHttpMethods());
        } elseif($this->isDefault && $routeDataMap->hasDefaultRouteData()) {
            $this->compileFound($code, $routeDataMap->getDefaultRouteData(), $parameters);
        } else {

            foreach ($routeDataMap->getHttpMethodRouteDataMap() as $item) {
                /** @var MatchedRouteData $routeData */
                list($httpMethods, $routeData) = $item;
                if(in_array($this->currentHttpMethod, $httpMethods)) {
                    $this->compileFound($code, $routeData, $parameters);
                    return;
                }
            }
        }
    }

    protected function compileNotFound(PhpBuilder $code)
    {
        if($this->gotoFallback) {
            $code->appendLine('goto ' . $this->gotoFallback . ';');
        } else {
            $code->appendLine('return $staticAllowedMethods ? Result::httpMethodNotAllowed($staticAllowedMethods) : Result::notFound();');
        }
    }

    protected function compileDisallowedHttpMethod(PhpBuilder $code, array $allowedMethod)
    {
        $code->appendLine('return Result::httpMethodNotAllowed(array_merge($staticAllowedMethods, ' . $this->export($allowedMethod) . '));');
    }

    protected function compileFound(PhpBuilder $code, MatchedRouteData $foundRoute, array $parameterExpressions)
    {
        $parameters = '[';

        foreach ($foundRoute->getParameterIndexNameMap() as $index => $parameterName) {
            $parameters .= $this->export($parameterName) . ' => ' . $parameterExpressions[$index] . ', ';
        }

        if (strlen($parameters) > 2) {
            $parameters = substr($parameters, 0, -2);
        }

        $parameters .= ']';


        $code->appendLine('return Result::found('
            . $this->export($foundRoute->getRouteData())
            . ', '
            . $parameters
            . ');'
        );
    }

    protected function export($value)
    {
        return VarExporter::export($value);
    }
}