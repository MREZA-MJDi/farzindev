<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Database\Seeders\ProvinceCitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutLocationPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_seeder_imports_all_provinces_and_real_cities_idempotently(): void
    {
        $this->seed(ProvinceCitySeeder::class);

        $this->assertSame(31, Province::query()->count());
        $this->assertSame(1481, City::query()->count());

        $this->seed(ProvinceCitySeeder::class);

        $this->assertSame(31, Province::query()->count());
        $this->assertSame(1481, City::query()->count());
    }

    public function test_public_city_endpoint_returns_only_cities_for_the_requested_province(): void
    {
        $this->seed(ProvinceCitySeeder::class);

        $province = Province::query()
            ->where('name', 'تهران')
            ->firstOrFail();

        $response = $this->getJson(route('api.provinces.cities', $province->id))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'province_id'],
                ],
            ]);

        $cities = $response->json('data');

        $this->assertNotEmpty($cities);
        foreach ($cities as $city) {
            $this->assertSame((int) $province->id, (int) $city['province_id']);
        }
    }

    public function test_checkout_rejects_a_city_that_does_not_belong_to_the_selected_province(): void
    {
        $this->seed(ProvinceCitySeeder::class);

        $tehran = Province::query()
            ->where('name', 'تهران')
            ->firstOrFail();

        $mazandaran = Province::query()
            ->where('name', 'مازندران')
            ->firstOrFail();

        $foreignCity = $mazandaran->cities()->firstOrFail();
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'first_name' => 'آزمون',
                'last_name' => 'مشتری',
                'phone' => '09123456789',
                'email' => 'checkout-location@example.test',
                'province_id' => $tehran->id,
                'city_id' => $foreignCity->id,
                'address' => 'خیابان نمونه، پلاک ۱۲',
                'postal_code' => '1234567890',
                'unit' => '۲',
                'notes' => '',
            ])
            ->assertSessionHasErrors('city_id');

        $this->assertDatabaseCount('orders', 0);
    }
}
