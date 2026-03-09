<?php

namespace Tests\Unit;

use App\Traits\HasVersions;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

class HasVersionsTest extends TestCase
{
    public function test_get_version_table_ends_with_versions_suffix(): void
    {
        $model = new class extends Model {
            use HasVersions;
        };

        // Anonymous class names are implementation-defined across PHP versions.
        // We verify the convention suffix only; named classes are tested in feature tests.
        $this->assertStringEndsWith('_versions', $model->getVersionTable());
    }

    public function test_get_version_foreign_key_derives_from_class_name(): void
    {
        $model = new class extends Model {
            use HasVersions;
        };

        $this->assertStringEndsWith('_id', $model->getVersionForeignKey());
    }

    public function test_get_versionable_fields_throws_when_fillable_is_empty(): void
    {
        $model = new class extends Model {
            use HasVersions;
            protected $fillable = [];
        };

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/fillable is empty/');

        $model->getVersionableFields();
    }

    public function test_get_versionable_fields_returns_fillable_by_default(): void
    {
        $model = new class extends Model {
            use HasVersions;
            protected $fillable = ['name', 'edrpou', 'address'];
        };

        $this->assertSame(['name', 'edrpou', 'address'], $model->getVersionableFields());
    }
}
