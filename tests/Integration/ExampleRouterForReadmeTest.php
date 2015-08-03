<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * This is the compiled router example from README.md,
 * conveniently will generate the compiled router when run.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ExampleRouterForReadmeTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'example-for-readme';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->param('post_slug', Pattern::ALPHA_NUM_DASH);

        $routes->get('/', ['name' => 'home']);
        $routes->get('/blog', ['name' => 'blog.index']);
        $routes->get('/blog/post/{post_slug}', ['name' => 'blog.post.show']);
        $routes->post('/blog/post/{post_slug}/comment', ['name' => 'blog.post.comment']);
    }

    /**
     * Should return each case in the format:
     *
     * [
     *      'GET',
     *      '/user/1',
     *      RouterResult::found(['route_data'], ['id' => '1'])
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

            ['DELETE', '/', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/blog/posts', MatchResult::notFound()],
            ['GET', '/blog/post/abc!@#', MatchResult::notFound()],
            ['PATCH', '/blog/post/123', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/blog/post/another-123-post/comment', MatchResult::httpMethodNotAllowed(['POST'])],
            ['PUT', '/blog/post/another-123-post/comment', MatchResult::httpMethodNotAllowed(['POST'])],
            ['PUT', '/blog/post/another-123-post/comment/foo', MatchResult::notFound()],
        ];
    }
}