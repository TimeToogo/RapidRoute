<?php

namespace RapidRoute\Compilation\RouteTree;

use RapidRoute\Compilation\Matchers\SegmentMatcher;

/**
 * The children nodes contents class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildrenNodeCollection extends  NodeContentsBase
{
    /**
     * @var RouteTreeNode[]|null
     */
    protected $children;

    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    /**
     * @return RouteTreeNode[]|null
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param RouteTreeNode $node
     *
     * @return bool
     */
    public function hasChild(RouteTreeNode $node)
    {
        return $this->hasChildFor($node->getFirstMatcher());
    }

    /**
     * @param SegmentMatcher $matcher
     *
     * @return bool
     */
    public function hasChildFor(SegmentMatcher $matcher)
    {
        $hash = $matcher->getHash();

        return isset($this->children[$hash]);
    }

    /**
     * @param SegmentMatcher $matcher
     *
     * @return RouteTreeNode|null
     */
    public function getChild(SegmentMatcher $matcher)
    {
        $hash = $matcher->getHash();

        return isset($this->children[$hash]) ? $this->children[$hash] : null;
    }

    /**
     * @param RouteTreeNode $node
     *
     * @return void
     */
    public function addChild(RouteTreeNode $node)
    {
        $hash = $node->getFirstMatcher()->getHash();

        $this->children[$hash] = $node;
    }
}