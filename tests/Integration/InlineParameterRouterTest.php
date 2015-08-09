<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InlineParameterRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'inline-route-parameters';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->get('/', ['name' => 'home']);
        $routes->get('/blog', ['name' => 'blog.index']);
        $routes->get('/blog/post/{post_slug:[a-z0-9\-]+}', ['name' => 'blog.post.show']);
        $routes->post('/blog/post/{post_slug:[a-z0-9\-]+}/comment', ['name' => 'blog.post.comment']);
        $routes->get('/blog/post/{post_slug:[a-z0-9\-]+}/comment/{comment_id:[0-9]+}', ['name' => 'blog.post.comment.show']);
    }

    /**
     * Should return each case in the format:
     *
     * [
     *      'GET',
     *      '/user/1',
     *      MatchResult::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingExamples()
    {
        return [
            ['GET', '/', MatchResult::found(['name' => 'home'], [])],
            ['HEAD', '/', MatchResult::found(['name' => 'home'], [])],
            ['GET', '/blog', MatchResult::found(['name' => 'blog.index'], [])],
            ['GET', '/blog/post/some-post', MatchResult::found(['name' => 'blog.post.show'], ['post_slug' => 'some-post'])],
            ['GET', '/blog/post/another-123-post', MatchResult::found(['name' => 'blog.post.show'], ['post_slug' => 'another-123-post'])],
            ['POST', '/blog/post/some-post/comment', MatchResult::found(['name' => 'blog.post.comment'], ['post_slug' => 'some-post'])],
            ['POST', '/blog/post/another-123-post/comment', MatchResult::found(['name' => 'blog.post.comment'], ['post_slug' => 'another-123-post'])],
            ['GET', '/blog/post/another-123-post/comment/123', MatchResult::found(['name' => 'blog.post.comment.show'], ['post_slug' => 'another-123-post', 'comment_id' => '123'])],

            ['DELETE', '/', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/blog/posts', MatchResult::notFound()],
            ['GET', '/blog/post/abc!@#', MatchResult::notFound()],
            ['GET', '/blog/post/aBc', MatchResult::notFound()],
            ['PATCH', '/blog/post/123', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/blog/post/another-123-post/comment', MatchResult::httpMethodNotAllowed(['POST'])],
            ['PUT', '/blog/post/another-123-post/comment', MatchResult::httpMethodNotAllowed(['POST'])],
            ['GET', '/blog/post/another-123-post/comment/foo', MatchResult::notFound()],
            ['GET', '/blog/post/another-123-post/comment/', MatchResult::notFound()],
            ['GET', '/blog/post/another-123-post/comment/-1', MatchResult::notFound()],
        ];
    }
}