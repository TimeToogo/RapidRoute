<?php

namespace RapidRoute\Tests\Integration;

use RapidRoute\Pattern;
use RapidRoute\RouteCollection;
use RapidRoute\MatchResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ComplexShopRouterTest extends RouterTestBase
{
    protected function compiledFileName()
    {
        return 'shop';
    }

    protected function definitions(RouteCollection $routes)
    {
        $routes->param('post_slug', Pattern::APLHA_NUM_DASH);
        $routes->param('category_id', Pattern::DIGITS);
        $routes->param('product_id', Pattern::DIGITS);
        $routes->param('filter_by', Pattern::APLHA);

        $routes->get('/', ['name' => 'home']);
        $routes->get('/about-us', ['name' => 'about-us']);
        $routes->get('/contact-us', ['name' => 'contact-us']);
        $routes->post('/contact-us', ['name' => 'contact-us.submit']);

        $routes->get('/blog', ['name' => 'blog.index']);
        $routes->get('/blog/recent', ['name' => 'blog.recent']);
        $routes->get('/blog/post/{post_slug}', ['name' => 'blog.post.show']);
        $routes->post('/blog/post/{post_slug}/comment', ['name' => 'blog.post.comment']);

        $routes->get('/shop', ['name' => 'shop.index']);

        $routes->get('/shop/category', ['name' => 'shop.category.index']);
        $routes->get('/shop/category/search/{filter_by}:{filter_value}', ['name' => 'shop.category.search']);
        $routes->get('/shop/category/{category_id}', ['name' => 'shop.category.show']);
        $routes->get('/shop/category/{category_id}/product', ['name' => 'shop.category.product.index']);
        $routes->get('/shop/category/{category_id}/product/search/{filter_by}:{filter_value}', ['name' => 'shop.category.product.search']);

        $routes->get('/shop/product', ['name' => 'shop.product.index']);
        $routes->get('/shop/product/search/{filter_by}:{filter_value}', ['name' => 'shop.product.search']);
        $routes->get('/shop/product/{product_id}', ['name' => 'shop.product.show']);

        $routes->get('/shop/cart', ['name' => 'shop.cart.show']);
        $routes->put('/shop/cart', ['name' => 'shop.cart.add']);
        $routes->delete('/shop/cart', ['name' => 'shop.cart.empty']);
        $routes->get('/shop/cart/checkout', ['name' => 'shop.cart.checkout.show']);
        $routes->post('/shop/cart/checkout', ['name' => 'shop.cart.checkout.process']);

        $routes->get('/admin/login', ['name' => 'admin.login']);
        $routes->post('/admin/login', ['name' => 'admin.login.submit']);
        $routes->get('/admin/logout', ['name' => 'admin.logout']);
        $routes->get('/admin', ['name' => 'admin.index']);

        $routes->get('/admin/product', ['name' => 'admin.product.index']);
        $routes->get('/admin/product/create', ['name' => 'admin.product.create']);
        $routes->post('/admin/product', ['name' => 'admin.product.store']);
        $routes->get('/admin/product/{product_id}', ['name' => 'admin.product.show']);
        $routes->get('/admin/product/{product_id}/edit', ['name' => 'admin.product.edit']);
        $routes->add(['PUT', 'PATCH'], '/admin/product/{product_id}', ['name' => 'admin.product.update']);
        $routes->delete('/admin/product/{product_id}', ['name' => 'admin.product.destroy']);

        $routes->get('/admin/category', ['name' => 'admin.category.index']);
        $routes->get('/admin/category/create', ['name' => 'admin.category.create']);
        $routes->post('/admin/category', ['name' => 'admin.category.store']);
        $routes->get('/admin/category/{category_id}', ['name' => 'admin.category.show']);
        $routes->get('/admin/category/{category_id}/edit', ['name' => 'admin.category.edit']);
        $routes->add(['PUT', 'PATCH'], '/admin/category/{category_id}', ['name' => 'admin.category.update']);
        $routes->delete('/admin/category/{category_id}', ['name' => 'admin.category.destroy']);
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
            ['GET', '', MatchResult::notFound()],
            ['GET', '/', MatchResult::found(['name' => 'home'], [])],

            ['GET', '/about-us', MatchResult::found(['name' => 'about-us'], [])],
            ['POST', '/about-us', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],

            ['GET', '/contact-us', MatchResult::found(['name' => 'contact-us'], [])],
            ['POST', '/contact-us', MatchResult::found(['name' => 'contact-us.submit'], [])],
            ['DELETE', '/contact-us', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],

            ['GET', '/blog', MatchResult::found(['name' => 'blog.index'], [])],
            ['PATCH', '/blog', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/blog/recent', MatchResult::found(['name' => 'blog.recent'], [])],
            ['GET', '/blog/abc', MatchResult::notFound()],
            ['GET', '/blog/post/123', MatchResult::found(['name' => 'blog.post.show'], ['post_slug' => '123'])],
            ['GET', '/blog/post/abc-123-qwerty', MatchResult::found(['name' => 'blog.post.show'], ['post_slug' => 'abc-123-qwerty'])],
            ['POST', '/blog/post/abc-123-qwerty', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['POST', '/blog/post/cool-post/comment', MatchResult::found(['name' => 'blog.post.comment'], ['post_slug' => 'cool-post'])],
            ['GET', '/blog/post/cool-post/comment', MatchResult::httpMethodNotAllowed(['POST'])],

            ['GET', '/shop', MatchResult::found(['name' => 'shop.index'], [])],
            ['DELETE', '/shop', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],

            ['GET', '/shop/category', MatchResult::found(['name' => 'shop.category.index'], [])],
            ['PUT', '/shop/category',  MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/shop/category/search/name:fun', MatchResult::found(['name' => 'shop.category.search'], ['filter_by' => 'name', 'filter_value' => 'fun'])],
            ['GET', '/shop/category/search/bad-prop:fun', MatchResult::notFound()],
            ['GET', '/shop/category/123', MatchResult::found(['name' => 'shop.category.show'], ['category_id' => '123'])],
            ['PATCH', '/shop/category/123', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/shop/category/-1', MatchResult::notFound()],
            ['GET', '/shop/category/abc', MatchResult::notFound()],
            ['GET', '/shop/category/123/product', MatchResult::found(['name' => 'shop.category.product.index'], ['category_id' => '123'])],
            ['GET', '/shop/category/123/product/search/name:cool', MatchResult::found(['name' => 'shop.category.product.search'], ['category_id' => '123', 'filter_by' => 'name', 'filter_value' => 'cool'])],
            ['GET', '/shop/category/123/product/epic', MatchResult::notFound()],

            ['GET', '/shop/product', MatchResult::found(['name' => 'shop.product.index'], [])],
            ['PUT', '/shop/product',  MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/shop/product/search/name:awesome', MatchResult::found(['name' => 'shop.product.search'], ['filter_by' => 'name', 'filter_value' => 'awesome'])],
            ['GET', '/shop/product/search/bad-prop:fun', MatchResult::notFound()],
            ['GET', '/shop/product/100', MatchResult::found(['name' => 'shop.product.show'], ['product_id' => '100'])],
            ['DELETE', '/shop/product/100', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],

            ['GET', '/shop/cart', MatchResult::found(['name' => 'shop.cart.show'], [])],
            ['PUT', '/shop/cart', MatchResult::found(['name' => 'shop.cart.add'], [])],
            ['DELETE', '/shop/cart', MatchResult::found(['name' => 'shop.cart.empty'], [])],
            ['PATCH', '/shop/cart', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'DELETE'])],
            ['GET', '/shop/cart/checkout', MatchResult::found(['name' => 'shop.cart.checkout.show'], [])],
            ['POST', '/shop/cart/checkout', MatchResult::found(['name' => 'shop.cart.checkout.process'], [])],
            ['GET', '/shop/cart/checkout/abc', MatchResult::notFound()],

            ['GET', '/admin/login', MatchResult::found(['name' => 'admin.login'], [])],
            ['POST', '/admin/login', MatchResult::found(['name' => 'admin.login.submit'], [])],
            ['HEAD', '/admin/login', MatchResult::found(['name' => 'admin.login'], [])],
            ['PATCH', '/admin/login',  MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],
            ['GET', '/admin/logout', MatchResult::found(['name' => 'admin.logout'], [])],
            ['GET', '/admin/logout/foo', MatchResult::notFound()],
            ['GET', '/admin', MatchResult::found(['name' => 'admin.index'], [])],
            ['GET', '/admin/', MatchResult::notFound()],

            ['GET', '/admin/product', MatchResult::found(['name' => 'admin.product.index'], [])],
            ['GET', '/admin/product/create', MatchResult::found(['name' => 'admin.product.create'], [])],
            ['TRACE', '/admin/product', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'POST'])],
            ['POST', '/admin/product/create', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/admin/product/1', MatchResult::found(['name' => 'admin.product.show'], ['product_id' => '1'])],
            ['HEAD', '/admin/product/123', MatchResult::found(['name' => 'admin.product.show'], ['product_id' => '123'])],
            ['GET', '/admin/product/abc', MatchResult::notFound()],
            ['GET', '/admin/product/1/edit', MatchResult::found(['name' => 'admin.product.edit'], ['product_id' => '1'])],
            ['PATCH', '/admin/product/1/edit', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['PUT', '/admin/product/1', MatchResult::found(['name' => 'admin.product.update'], ['product_id' => '1'])],
            ['PATCH', '/admin/product/1', MatchResult::found(['name' => 'admin.product.update'], ['product_id' => '1'])],
            ['POST', '/admin/product/1', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'PATCH', 'DELETE'])],
            ['DELETE', '/admin/product/2', MatchResult::found(['name' => 'admin.product.destroy'], ['product_id' => '2'])],
            ['TRACE', '/admin/product/123', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'PATCH', 'DELETE'])],

            ['GET', '/admin/category', MatchResult::found(['name' => 'admin.category.index'], [])],
            ['GET', '/admin/category/create', MatchResult::found(['name' => 'admin.category.create'], [])],
            ['POST', '/admin/category/create', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['GET', '/admin/category/1', MatchResult::found(['name' => 'admin.category.show'], ['category_id' => '1'])],
            ['GET', '/admin/category/abc', MatchResult::notFound()],
            ['GET', '/admin/category/1/edit', MatchResult::found(['name' => 'admin.category.edit'], ['category_id' => '1'])],
            ['PATCH', '/admin/category/1/edit', MatchResult::httpMethodNotAllowed(['GET', 'HEAD'])],
            ['PUT', '/admin/category/1', MatchResult::found(['name' => 'admin.category.update'], ['category_id' => '1'])],
            ['PATCH', '/admin/category/1', MatchResult::found(['name' => 'admin.category.update'], ['category_id' => '1'])],
            ['POST', '/admin/category/1', MatchResult::httpMethodNotAllowed(['GET', 'HEAD', 'PUT', 'PATCH', 'DELETE'])],
            ['DELETE', '/admin/category/2', MatchResult::found(['name' => 'admin.category.destroy'], ['category_id' => '2'])],
        ];
    }
}