<?php

namespace Tests\Feature;

use App\Support\DemoData;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/**
 * Smoke test: every named admin.* GET route and the placeholder API render with HTTP 200.
 * New admin routes are picked up automatically; add a sample value to $params
 * when a new route parameter is introduced.
 */
class AdminPagesTest extends TestCase
{
    /** Sample values for route parameters. */
    private function params(): array
    {
        return [
            'order' => 'WB-10482',
            'product' => (string) DemoData::adminProducts()[0]['id'],
        ];
    }

    public function test_root_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('admin.dashboard'));
    }

    public function test_every_named_admin_get_route_returns_200(): void
    {
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $r) => str_starts_with((string) $r->getName(), 'admin.') && in_array('GET', $r->methods(), true));

        $this->assertGreaterThanOrEqual(28, $routes->count(), 'Expected all admin pages to be registered.');

        foreach ($routes as $route) {
            $args = [];
            foreach ($route->parameterNames() as $name) {
                $this->assertArrayHasKey($name, $this->params(), "No sample value for route parameter {{$name}}.");
                $args[$name] = $this->params()[$name];
            }

            $url = route($route->getName(), $args);
            $response = $this->get($url);

            $this->assertSame(200, $response->status(), "GET {$url} ({$route->getName()}) returned {$response->status()}.");
        }
    }

    public function test_every_demo_order_and_product_page_renders(): void
    {
        foreach (DemoData::orders() as $order) {
            $this->get(route('admin.orders.show', $order['id']))->assertOk()->assertSee($order['number']);
        }
        foreach (DemoData::products() as $product) {
            $this->get(route('admin.products.edit', $product['id']))->assertOk();
        }
    }

    public function test_unknown_order_and_product_return_404(): void
    {
        $this->get(route('admin.orders.show', 'NOPE-1'))->assertNotFound();
        $this->get(route('admin.products.edit', 9999))->assertNotFound();
    }

    public function test_dashboard_shows_design_content(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('৳48,250')
            ->assertSee('#WB-10482')
            ->assertSee('PNJ-1024-OW-XXL')
            ->assertSee('Embroidered Cotton Panjabi')
            ->assertSee('Returns &amp; exchanges', false);
    }

    public function test_login_page_has_both_steps(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Staff login')
            ->assertSee('Enter verification code')
            ->assertSee(route('admin.dashboard'));
    }

    public function test_api_routes_return_json(): void
    {
        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'slug', 'name', 'price', 'image_url']]]);

        $this->getJson('/api/products/embroidered-cotton-panjabi')
            ->assertOk()
            ->assertJsonPath('data.sku', 'PNJ-1024')
            ->assertJsonStructure(['data' => ['variants' => [['size', 'colour', 'sku', 'stock']]]]);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);

        $this->getJson('/api/products/does-not-exist')->assertNotFound();
    }

    public function test_money_uses_indian_grouping(): void
    {
        $this->assertSame('৳2,14,600', DemoData::money(214600));
        $this->assertSame('৳14,82,600', DemoData::money(1482600));
        $this->assertSame('৳4,705', DemoData::money(4705));
        $this->assertSame('৳515', DemoData::money(515));
        $this->assertSame('−৳515', DemoData::money(-515));
        $this->assertSame('৳1,00,00,000', DemoData::money(10000000));
    }

    public function test_order_totals_add_up(): void
    {
        foreach (DemoData::orders() as $o) {
            $coupon = $o['coupon']['amount'] ?? 0;
            $this->assertSame(
                $o['amount'],
                $o['subtotal'] - $coupon - $o['discount'] + $o['delivery_fee'],
                "Totals for {$o['number']} do not add up."
            );
        }
    }
}
