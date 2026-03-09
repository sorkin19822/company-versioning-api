<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasVersions
{
    /**
     * Boot the trait — automatically called by Eloquent via bootHasVersions() convention.
     * Registers created/updated event listeners to snapshot model state.
     */
    public static function bootHasVersions(): void
    {
        static::created(function (Model $model) {
            $model->saveVersion();
        });

        static::updated(function (Model $model) {
            // Only snapshot when at least one versionable field actually changed.
            // Eloquent fires `updated` on any attribute change, including non-versionable ones.
            $changedFields = array_keys($model->getChanges());
            $versionableFields = $model->getVersionableFields();

            if (!empty(array_intersect($versionableFields, $changedFields))) {
                $model->saveVersion();
            }
        });
    }

    /**
     * Shared base key derived from the model class name.
     * e.g. Company → "company", ProductPrice → "product_price"
     */
    protected function versionBaseKey(): string
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * Name of the versions table.
     * Default: snake_case(ModelName) + '_versions' (e.g. Company → company_versions).
     * Override in the model to customise.
     */
    public function getVersionTable(): string
    {
        return $this->versionBaseKey() . '_versions';
    }

    /**
     * Foreign key column name in the versions table.
     * Default: snake_case(ModelName) + '_id' (e.g. Company → company_id).
     * Override in the model to customise.
     */
    public function getVersionForeignKey(): string
    {
        return $this->versionBaseKey() . '_id';
    }

    /**
     * Attributes to snapshot on each version.
     * Defaults to $fillable. Override in the model for explicit field control.
     *
     * NOTE: $fillable must not be empty. If your model uses $guarded instead,
     * override this method and return the list of fields to version explicitly.
     *
     * NOTE: The versions table uses unsignedSmallInteger for the version column,
     * capping at 65,535 versions per record.
     *
     * @return array<string>
     * @throws \LogicException if $fillable is empty and method is not overridden.
     */
    public function getVersionableFields(): array
    {
        $fields = $this->getFillable();

        if (empty($fields)) {
            throw new \LogicException(
                static::class . ' uses HasVersions but $fillable is empty. '
                . 'Override getVersionableFields() to declare which attributes to version.'
            );
        }

        return $fields;
    }

    /**
     * Inserts a new snapshot row into the versions table.
     *
     * Uses a DB transaction with a pessimistic lock (lockForUpdate) to prevent
     * race conditions when two concurrent requests version the same record.
     * The UNIQUE(foreign_key, version) DB constraint acts as a final safety net.
     */
    public function saveVersion(): void
    {
        $table      = $this->getVersionTable();
        $foreignKey = $this->getVersionForeignKey();

        DB::transaction(function () use ($table, $foreignKey) {
            $currentMax  = DB::table($table)
                ->where($foreignKey, $this->getKey())
                ->lockForUpdate()
                ->max('version');

            $nextVersion = (int) $currentMax + 1;

            $snapshot = [];
            foreach ($this->getVersionableFields() as $field) {
                $snapshot[$field] = $this->getAttribute($field);
            }

            DB::table($table)->insert(array_merge(
                [$foreignKey => $this->getKey(), 'version' => $nextVersion],
                $snapshot,
                ['created_at' => now()]
            ));
        });
    }

    /**
     * Returns the current (latest) version number for this record.
     * Returns 0 if no versions exist yet (sentinel value — version numbering starts at 1).
     */
    public function getCurrentVersion(): int
    {
        return (int) DB::table($this->getVersionTable())
            ->where($this->getVersionForeignKey(), $this->getKey())
            ->max('version');
    }
}
