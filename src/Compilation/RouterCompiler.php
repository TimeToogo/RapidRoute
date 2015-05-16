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
use RapidRoute\MatchResult as Result;

return function ($method, $uri) {
    if($uri === '') {
{root_route}
    } elseif ($uri[0] !== '/') {
        throw new RapidRouteException("Cannot match route: non-empty uri must be prefixed with '/', '{$uri}' given");
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
        $routeTree = $this->treeBuilder->build($routes);
        $routeTree = $this->treeOptimizer->optimize($routeTree);

        $code         = new PhpBuilder();
        $code->indent = 1;
        $this->compileRouteTree($code, $routeTree);

        $rootRouteCode = new PhpBuilder();
        $rootRouteCode->indent = 2;
        if ($routeTree->hasRootRoute()) {
            $this->compiledRouteHttpMethodMatch($rootRouteCode, $routeTree->getRootRouteData(), array());
        } else {
            $this->compileNotFound($rootRouteCode);
        }

        return $this->formatPhpRouterTemplate(substr($rootRouteCode->getCode(), 0, -strlen(PHP_EOL)), $code->getCode());
    }

    protected function formatPhpRouterTemplate($rootRoute, $body)
    {
        return strtr(self::COMPILED_ROUTER_TEMPLATE, ['{root_route}' => $rootRoute, '{body}' => $body]);
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
        $code->appendLine('switch ($method) {');
        $code->indent++;

        foreach ($routeDataMap->getHttpMethodRouteDataMap() as $item) {
            /** @var MatchedRouteData $routeData */
            list($httpMethods, $routeData) = $item;
            foreach ($httpMethods as $httpMethod) {
                $code->appendLine('case ' . $this->export($httpMethod) . ':');
            }

            $code->indent++;
            $this->compileFound($code, $routeData, $parameters);
            $code->indent--;
        }

        $code->appendLine('default:');
        $code->indent++;

        if ($routeDataMap->hasDefaultRouteData()) {
            $this->compileFound($code, $routeDataMap->getDefaultRouteData(), $parameters);
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