<?php

namespace RapidRoute\Compilation\RouteTree;

use RapidRoute\Compilation\Matchers\SegmentMatcher;
use RapidRoute\RapidRouteException;

/**
 * The base route tree node class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RouteTreeNode
{
    /**
     * @var SegmentMatcher[]
     */
    protected $matchers;

    /**
     * @var ChildrenNodeCollection|MatchedRouteDataMap
     */
    protected $contents;

    public function __construct(array $matchers, NodeContentsBase $contents)
    {
        if(empty($matchers)) {
            throw new RapidRouteException(sprintf('Cannot construct %s: matchers must not be empty', __CLASS__));
        }

        $this->matchers = $matchers;
        $this->contents = $contents;
    }

    /**
     * @param SegmentMatcher[] $matchers
     * @param bool             $isLeafNode
     *
     * @return RouteTreeNode
     */
    public static function create(array $matchers, $isLeafNode)
    {
        return new self($matchers, $isLeafNode ? new MatchedRouteDataMap() : new ChildrenNodeCollection());
    }

    /**
     * @return SegmentMatcher[]
     */
    public function getMatchers()
    {
        return $this->matchers;
    }

    /**
     * @return SegmentMatcher
     */
    public function getFirstMatcher()
    {
        return $this->matchers[min(array_keys($this->matchers))];
    }

    /**
     * @return bool
     */
    public function isLeafNode()
    {
        return $this->contents instanceof MatchedRouteDataMap;
    }

    /**
     * @return bool
     */
    public function isParentNode()
    {
        return $this->contents instanceof ChildrenNodeCollection;
    }

    /**
     * @return ChildrenNodeCollection|MatchedRouteDataMap
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param array            $matchers
     * @param NodeContentsBase $contents
     *
     * @return RouteTreeNode
     */
    public function update(array $matchers, NodeContentsBase $contents)
    {
        if($this->matchers === $matchers && $this->contents === $contents) {
            return $this;
        }

        $clone = clone $this;
        $clone->matchers = $matchers;
        $clone->contents = $contents;

        return $clone;
    }
}