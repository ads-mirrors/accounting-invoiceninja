<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\MockAccountData;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use DatabaseTransactions;
    use MockAccountData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeTestData();
        Session::start();
    }

    public function testLocationPost()
    {
        $data = [
            'name' => 'Test Location',
            'address1' => '123 Test St',
            'address2' => 'Suite 100',
            'city' => 'Test City',
            'state' => 'TS',
            'postal_code' => '12345',
            'country_id' => '840', // USA
        ];

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/locations', $data);

        $response->assertStatus(200);

        $arr = $response->json();
        $this->assertEquals($data['name'], $arr['data']['name']);
        $this->assertEquals($data['address1'], $arr['data']['address1']);
    }

    public function testLocationGet()
    {
        $location = Location::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->get('/api/v1/locations/' . $this->encodePrimaryKey($location->id));

        $response->assertStatus(200);

        $arr = $response->json();
        $this->assertEquals($location->name, $arr['data']['name']);
    }

    public function testLocationPut()
    {
        $location = Location::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $data = [
            'name' => 'Updated Location',
            'address1' => '456 Update St',
        ];

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->putJson('/api/v1/locations/' . $this->encodePrimaryKey($location->id), $data);

        $response->assertStatus(200);

        $arr = $response->json();
        $this->assertEquals($data['name'], $arr['data']['name']);
        $this->assertEquals($data['address1'], $arr['data']['address1']);
    }

    public function testLocationDelete()
    {
        $location = Location::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->deleteJson('/api/v1/locations/' . $this->encodePrimaryKey($location->id));

        $response->assertStatus(200);
    }

    public function testLocationList()
    {
        Location::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->get('/api/v1/locations');

        $response->assertStatus(200);

        $arr = $response->json();
        $this->assertCount(3, $arr['data']);
    }

    public function testLocationValidation()
    {
        $data = [
            'name' => '', // Required field is empty
        ];

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/locations', $data);

        $response->assertStatus(422);
    }

    public function testBulkActions()
    {
        $locations = Location::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);

        $data = [
            'action' => 'archive',
            'ids' => $locations->pluck('hashed_id')->values()->toArray(),
        ];

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/locations/bulk', $data);

        $response->assertStatus(200);

        foreach ($locations as $location) {
            $this->assertNotNull($location->fresh()->deleted_at);
        }
    }

    public function testLocationRestore()
    {
        $location = Location::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'deleted_at' => now(),
        ]);

        $data = [
            'action' => 'restore',
            'ids' => [$location->hashed_id],
        ];

        $response = $this->withHeaders([
            'X-API-TOKEN' => $this->token,
        ])->postJson('/api/v1/locations/bulk', $data);

        $response->assertStatus(200);

        $this->assertNull($location->fresh()->deleted_at);
    }
}
