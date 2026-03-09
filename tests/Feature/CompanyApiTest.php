<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name'    => 'ТОВ Українська енергетична біржа',
        'edrpou'  => '37027819',
        'address' => '01001, Україна, м. Київ, вул. Хрещатик, 44',
    ];

    // -------------------------------------------------------------------------
    // POST /api/company
    // -------------------------------------------------------------------------

    public function test_creates_new_company_returns_201(): void
    {
        $response = $this->postJson('/api/company', $this->payload);

        $response->assertStatus(201)
                 ->assertJsonStructure(['status', 'company_id', 'version'])
                 ->assertJson(['status' => 'created', 'version' => 1]);

        $this->assertDatabaseHas('companies', ['edrpou' => '37027819']);
        $this->assertDatabaseHas('company_versions', ['edrpou' => '37027819', 'version' => 1]);
    }

    public function test_updates_company_when_data_changed_returns_200(): void
    {
        $this->postJson('/api/company', $this->payload);

        $updated  = array_merge($this->payload, ['address' => 'Нова адреса, 1']);
        $response = $this->postJson('/api/company', $updated);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'updated', 'version' => 2]);

        $this->assertSame(2, CompanyVersion::where('edrpou', '37027819')->count());
    }

    public function test_returns_duplicate_when_data_unchanged(): void
    {
        $this->postJson('/api/company', $this->payload);
        $response = $this->postJson('/api/company', $this->payload);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'duplicate', 'version' => 1]);

        $this->assertSame(1, CompanyVersion::where('edrpou', '37027819')->count());
    }

    public function test_multiple_updates_increment_version_sequentially(): void
    {
        $this->postJson('/api/company', $this->payload);
        $this->postJson('/api/company', array_merge($this->payload, ['address' => 'Адреса 2']));
        $this->postJson('/api/company', array_merge($this->payload, ['address' => 'Адреса 3']));

        $response = $this->postJson('/api/company', array_merge($this->payload, ['address' => 'Адреса 4']));

        $response->assertJson(['status' => 'updated', 'version' => 4]);
        $this->assertSame(4, CompanyVersion::where('edrpou', '37027819')->count());
    }

    public function test_only_changed_versionable_field_triggers_update(): void
    {
        $this->postJson('/api/company', $this->payload);

        $response = $this->postJson('/api/company', array_merge($this->payload, ['name' => 'Нова назва']));

        $response->assertJson(['status' => 'updated', 'version' => 2]);
    }

    public function test_whitespace_trimming_does_not_create_false_update(): void
    {
        $this->postJson('/api/company', $this->payload);

        // Same data but with extra whitespace — should be treated as duplicate after trim
        $response = $this->postJson('/api/company', array_merge($this->payload, [
            'name'    => '  ТОВ Українська енергетична біржа  ',
            'address' => '  01001, Україна, м. Київ, вул. Хрещатик, 44  ',
        ]));

        $response->assertJson(['status' => 'duplicate', 'version' => 1]);
    }

    public function test_version_snapshot_stores_correct_data(): void
    {
        $this->postJson('/api/company', $this->payload);

        $version = CompanyVersion::where('edrpou', '37027819')->first();

        $this->assertSame('ТОВ Українська енергетична біржа', $version->name);
        $this->assertSame('37027819', $version->edrpou);
        $this->assertSame('01001, Україна, м. Київ, вул. Хрещатик, 44', $version->address);
        $this->assertSame(1, $version->version);
        $this->assertNotNull($version->created_at);
    }

    // -------------------------------------------------------------------------
    // Validation — POST /api/company
    // -------------------------------------------------------------------------

    public function test_validation_fails_when_name_missing(): void
    {
        $this->postJson('/api/company', array_merge($this->payload, ['name' => '']))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_fails_when_name_too_short(): void
    {
        $this->postJson('/api/company', array_merge($this->payload, ['name' => 'A']))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_fails_when_name_exceeds_max_length(): void
    {
        $this->postJson('/api/company', array_merge($this->payload, ['name' => str_repeat('a', 257)]))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_fails_when_edrpou_missing(): void
    {
        $payload = $this->payload;
        unset($payload['edrpou']);

        $this->postJson('/api/company', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['edrpou']);
    }

    public function test_validation_fails_when_edrpou_contains_letters(): void
    {
        $this->postJson('/api/company', array_merge($this->payload, ['edrpou' => 'abc12345']))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['edrpou']);
    }

    public function test_validation_fails_when_edrpou_exceeds_max_length(): void
    {
        $this->postJson('/api/company', array_merge($this->payload, ['edrpou' => '12345678901']))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['edrpou']);
    }

    public function test_validation_fails_when_address_missing(): void
    {
        $payload = $this->payload;
        unset($payload['address']);

        $this->postJson('/api/company', $payload)
             ->assertStatus(422)
             ->assertJsonValidationErrors(['address']);
    }

    public function test_validation_fails_when_all_fields_missing(): void
    {
        $this->postJson('/api/company', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'edrpou', 'address']);
    }

    // -------------------------------------------------------------------------
    // GET /api/company/{edrpou}/versions
    // -------------------------------------------------------------------------

    public function test_versions_endpoint_returns_all_versions(): void
    {
        $this->postJson('/api/company', $this->payload);
        $this->postJson('/api/company', array_merge($this->payload, ['address' => 'Нова адреса']));

        $response = $this->getJson('/api/company/37027819/versions');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'company_id',
                     'edrpou',
                     'versions' => [
                         '*' => ['id', 'version', 'name', 'edrpou', 'address', 'created_at'],
                     ],
                 ])
                 ->assertJsonCount(2, 'versions')
                 ->assertJsonPath('versions.0.version', 1)
                 ->assertJsonPath('versions.1.version', 2);
    }

    public function test_versions_endpoint_returns_404_for_unknown_edrpou(): void
    {
        $this->getJson('/api/company/99999999/versions')
             ->assertStatus(404);
    }

    public function test_versions_endpoint_returns_404_for_non_digit_edrpou(): void
    {
        $this->getJson('/api/company/abc123/versions')
             ->assertStatus(404);
    }

    public function test_versions_endpoint_returns_404_for_edrpou_exceeding_max_length(): void
    {
        $this->getJson('/api/company/12345678901/versions')
             ->assertStatus(404);
    }
}
