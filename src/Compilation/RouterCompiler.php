<?php

namespace RapidRoute\Compilation;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\RouteTree\ChildrenNodeCollection;
use RapidRoute\Compilation\RouteTree\MatchedRouteData;
use RapidRoute\Compilation\RouteTree\MatchedRouteDataMap;
use RapidRoute\Compilation\RouteTree\RouteTree;
use RapidRoute\Compilation\RouteTree\RouteTreeBuilder;
use RapidRoute\Compilation\RouteTree\RouteTreeOptimizer;
use RapidRoute\RouteCollection;

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
use RapidRoute\RouterResult as Result;

return function ($method, $uri) {
    $segments = explode('/', $uri);

    if($segments[0] !== '') {
        throw new RapidRouteException("Cannot match route: non-empty uri must be prefixed with '/', '{$uri}' given");
    }

    array_shift($segments);
    $parameters = [];

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
        $routeTree = $this->treeBuilder->build($routes);
        $routeTree = $this->treeOptimizer->optimize($routeTree);

        $code         = new PhpBuilder();
        $code->indent = 1;
        $this->compileRouteTree($code, $routeTree);

        return $this->formatPhpRouterTemplate($code->getCode());
    }

    /**
     * @param string $php
     *
     * @return string
     */
    protected function formatPhpRouterTemplate($php)
    {
        return str_replace('{body}', $php, self::COMPILED_ROUTER_TEMPLATE);
    }

    protected function compileRouteTree(PhpBuilder $code, RouteTree $routeTree)
    {
        $code->appendLine('switch (count($segments)) {');
        $code->indent++;

        if ($routeTree->hasRootRoute()) {
            $code->appendLine('case 0:');
            $code->indent++;
            $this->compiledRouteHttpMethodMatch($code, $routeTree->getRootRouteData());
            $code->appendLine('break;');
            $code->indent--;
        }

        foreach ($routeTree->getSegmentDepthNodesMap() as $segmentDepth => $nodes) {
            $code->appendLine('case ' . $this->export($segmentDepth) . ':');
            $code->indent++;
            $this->compileSegmentNodes($code, $nodes);
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

    protected function compileSegmentNodes(PhpBuilder $code, ChildrenNodeCollection $nodes)
    {
        $code->appendLine('switch (true) {');
        $code->indent++;

        foreach ($nodes->getChildren() as $node) {
            /** @var SegmentMatcher[] $segmentMatchers */
            $segmentVariables = [];
            $segmentMatchers  = $node->getMatchers();

            $conditions       = [];

            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $segmentVariables[$segmentDepth] = '$segments[' . $segmentDepth . ']';
            }

            $count = 0;
            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $conditions[] = $matcher->getConditionExpression($segmentVariables[$segmentDepth], $count++);
            }

            $code->appendLine('case ' . implode(' && ', $conditions) . ':');
            $code->indent++;

            $count = 0;
            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $matchedParameters = $matcher->getMatchedParameterExpressions($segmentVariables[$segmentDepth], $count++);

                foreach($matchedParameters as $parameterKey => $matchedParameter) {
                    $code->appendLine('$parameters[' . $parameterKey . '] = ' . $matchedParameter . ';');
                }
            }

            if ($node->isLeafNode()) {
                $this->compiledRouteHttpMethodMatch($code, $node->getContents());
            } else {
                $this->compileSegmentNodes($code, $node->getContents());
            }

            $code->appendLine('break;');
            $code->indent--;
        }

        $code->appendLine('default:');
        $code->indent++;
        $this->compileNotFound($code);
        $code->indent--;

        $code->indent--;
        $code->appendLine('}');
    }

    protected function compiledRouteHttpMethodMatch(PhpBuilder $code, MatchedRouteDataMap $routeDataMap)
    {
        $code->appendLine('switch ($method) {');
        $code->indent++;

        foreach ($routeDataMap->getHttpMethodRouteDataMap() as $item) {
            /** @var MatchedRouteData $routeData */
            list($httpMethods, $routeData) = $item;
            foreach ($httpMethods as $httpMethod) {
                $code->appendLine('case ' . $this->export($httpMethod) . ':');
            }

            $code->indent++;
            $this->compileFound($code, $routeData);
            $code->indent--;
        }

        $code->appendLine('default:');
        $code->indent++;

        if ($routeDataMap->hasDefaultRouteData()) {
            $this->compileFound($code, $routeDataMap->getDefaultRouteData());
        } else {
            $this->compileDisallowedHttpMethod($code, $routeDataMap->getAllowedHttpMethods());
        }

        $code->indent--;

        $code->indent--;
        $code->appendLine('}');
    }

    protected function compileNotFound(PhpBuilder $code)
    {
        $code->appendLine('return Result::notFound();');
    }

    protected function compileDisallowedHttpMethod(PhpBuilder $code, array $allowedMethod)
    {
        $code->appendLine('return Result::httpMethodNotAllowed(' . $this->export($allowedMethod) . ');');
    }

    protected function compileFound(PhpBuilder $code, MatchedRouteData $foundRoute)
    {
        $parameters = '[';

        foreach ($foundRoute->getParameterIndexNameMap() as $index => $parameterName) {
            $parameters .= $this->export($parameterName) . ' => $parameters[' . $index . '], ';
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