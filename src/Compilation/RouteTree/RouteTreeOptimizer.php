<?php

namespace RapidRoute\Compilation\RouteTree;

use RapidRoute\Compilation\Matchers\AnyMatcher;
use RapidRoute\Compilation\Matchers\CompoundMatcher;
use RapidRoute\Compilation\Matchers\ExpressionMatcher;
use RapidRoute\Compilation\Matchers\RegexMatcher;
use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\Compilation\Matchers\StaticMatcher;
use RapidRoute\Pattern;
use RapidRoute\RouteSegments\RouteSegment;

/**
 * The route tree builder class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeOptimizer
{
    /**
     * Optimizes the supplied route tree
     *
     * @param RouteTree $routeTree
     *
     * @return RouteTree
     */
    public function optimize(RouteTree $routeTree)
    {
        $segmentDepthNodeMap = $routeTree->getSegmentDepthNodesMap();

        foreach ($segmentDepthNodeMap as $segmentDepth => $nodes) {
            $segmentDepthNodeMap[$segmentDepth] = $this->optimizeNodes($nodes);
        }

        return new RouteTree($routeTree->getRootRouteData(), $segmentDepthNodeMap);
    }

    /**
     * @param ChildrenNodeCollection $nodes
     *
     * @return ChildrenNodeCollection
     */
    protected function optimizeNodes(ChildrenNodeCollection $nodes)
    {
        $optimizedNodes = [];

        foreach ($nodes->getChildren() as $key => $node) {
            $optimizedNodes[$key] = $this->optimizeNode($node);
        }

        $optimizedNodes = new ChildrenNodeCollection($optimizedNodes);

        $optimizedNodes = $this->moveCommonMatchersToParentNode($optimizedNodes);

        return $optimizedNodes;
    }

    protected function optimizeNode(RouteTreeNode $node)
    {
        $matchers = $node->getMatchers();
        $contents = $node->getContents();

        if ($node->isParentNode()) {
            $contents = $this->optimizeNodes($node->getContents());
            $children = $contents->getChildren();

            if (count($children) === 1) {
                /** @var RouteTreeNode $childNode */
                $childNode = reset($children);

                $matchers = $this->mergeMatchers($node->getMatchers(), $childNode->getMatchers());
                $contents = $childNode->getContents();
            }
        }

        $matchers = $this->optimizeMatchers($matchers);

        return $node->update($matchers, $contents);
    }

    /**
     * @param RouteSegment[] $parentMatchers
     * @param RouteSegment[] $childMatchers
     *
     * @return RouteSegment[]
     */
    protected function mergeMatchers(array $parentMatchers, array $childMatchers)
    {
        $mergedMatchers = $parentMatchers;

        foreach($childMatchers as $segment => $childMatcher) {
            if(isset($mergedMatchers[$segment])) {
                $mergedMatchers[$segment] = new CompoundMatcher([$mergedMatchers[$segment], $childMatcher]);
            } else {
                $mergedMatchers[$segment] = $childMatcher;
            }
        }

        return $mergedMatchers;
    }

    /**
     * @param SegmentMatcher[] $matchers
     *
     * @return SegmentMatcher[]
     */
    protected function optimizeMatchers(array $matchers)
    {
        foreach($matchers as $key => $matcher) {
            $matchers[$key] = $this->optimizeMatcher($matcher);
        }

        return $this->optimizeMatcherOrder($matchers);
    }

    /**
     * @param SegmentMatcher $matcher
     *
     * @return SegmentMatcher
     */
    protected function optimizeMatcher(SegmentMatcher $matcher)
    {
        if($matcher instanceof RegexMatcher && count($matcher->getParameterKeys()) === 1) {
            $parameterKey = $matcher->getParameterKeys()[0];

            switch($matcher->regex) {
                case Pattern::asRegex(Pattern::ANY):
                    return new AnyMatcher($parameterKey);

                case Pattern::asRegex(Pattern::DIGITS):
                    return new ExpressionMatcher('ctype_digit({segment})', $parameterKey);

                case Pattern::asRegex(Pattern::APLHA):
                    return new ExpressionMatcher('ctype_alpha({segment})', $parameterKey);

                case Pattern::asRegex(Pattern::APLHA_LOWER):
                    return new ExpressionMatcher('ctype_lower({segment})', $parameterKey);

                case Pattern::asRegex(Pattern::APLHA_UPPER):
                    return new ExpressionMatcher('ctype_upper({segment})', $parameterKey);

                case Pattern::asRegex(Pattern::APLHA_NUM):
                    return new ExpressionMatcher('ctype_alnum({segment})', $parameterKey);

                case Pattern::asRegex(Pattern::APLHA_NUM_DASH):
                    return new ExpressionMatcher('ctype_alnum(str_replace(\'-\', \'\', {segment}))', $parameterKey);
            }
        }

        return $matcher;
    }

    protected function optimizeMatcherOrder(array $matchers)
    {
        $computationalCostOrder = [
            AnyMatcher::getType(),
            StaticMatcher::getType(),
            ExpressionMatcher::getType(),
            RegexMatcher::getType(),
            // Unknown types last
            SegmentMatcher::getType()
        ];

        $groups = [];

        foreach($computationalCostOrder as $type) {
            foreach($matchers as $index => $matcher) {
                if($matcher instanceof $type) {
                    unset($matchers[$index]);
                    $groups[$type][$index] = $matcher;
                }
            }
        }

        $matchers = [];

        foreach($groups as $group) {
            foreach($group as $index => $matcher) {
                $matchers[$index] = $matcher;
            }
        }

        return $matchers;
    }

    /**
     * @param ChildrenNodeCollection $nodeCollection
     *
     * @return ChildrenNodeCollection
     */
    protected function moveCommonMatchersToParentNode(ChildrenNodeCollection $nodeCollection)
    {
        $nodes = $nodeCollection->getChildren();
        if(count($nodes) <= 1) {
            return $nodeCollection;
        }

        $children = [];
        $previous = array_shift($nodes);

        foreach($nodes as $node) {
            $parent = $this->extractCommonParentNode($previous, $node);

            if($parent) {
                $previous = $parent;
            } else {
                $children[] = $previous;
                $previous = $node;
            }
        }
        $children[] = $previous;

        return new ChildrenNodeCollection($children);
    }

    /**
     * @param RouteTreeNode $node1
     * @param RouteTreeNode $node2
     *
     * @return RouteTreeNode|null
     */
    protected function extractCommonParentNode(RouteTreeNode $node1, RouteTreeNode $node2)
    {
        $matcherCompare = function (SegmentMatcher $a, SegmentMatcher $b) {
            return strcmp($a->getHash(), $b->getHash());
        };

        $commonMatchers = array_uintersect_assoc($node1->getMatchers(), $node2->getMatchers(), $matcherCompare);

        if(empty($commonMatchers)) {
            return null;
        }

        $children = [];

        /** @var RouteTreeNode[] $nodes */
        $nodes = [$node1, $node2];

        foreach($nodes as $node) {
            $specificMatchers = array_udiff_assoc($node->getMatchers(), $commonMatchers, $matcherCompare);

            if(empty($specificMatchers) && $node->isParentNode()) {
                foreach($node->getContents()->getChildren() as $childNode) {
                    $children[] = $childNode;
                }
            } else {
                $children[] = $node->update($specificMatchers, $node->getContents());
            }
        }

        return new RouteTreeNode($commonMatchers, ChildrenNodeCollection::nonExclusive($children));
    }
}