<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Create or update a company by edrpou, applying versioning rules.
     *
     * Callers must validate $data before invoking this method (e.g. via CompanyRequest).
     * Expected keys: name, edrpou, address.
     *
     * @param  array{name: string, edrpou: string, address: string} $data
     * @return array{status: string, company_id: int, version: int}
     */
    public function upsert(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $company = Company::where('edrpou', $data['edrpou'])
                ->lockForUpdate()
                ->first();

            if ($company === null) {
                try {
                    $company = Company::create($data);

                    return $this->result('created', $company);
                } catch (UniqueConstraintViolationException) {
                    // Two concurrent requests both saw null and raced to insert.
                    // The unique index on edrpou caught the collision — re-fetch
                    // the winner's record and fall through to update/duplicate logic.
                    $company = Company::where('edrpou', $data['edrpou'])->firstOrFail();
                }
            }

            $fields   = $company->getVersionableFields();
            $incoming = $this->normalizeFields(Arr::only($data, $fields));
            $existing = $this->normalizeFields(Arr::only($company->getAttributes(), $fields));

            if ($incoming === $existing) {
                return $this->result('duplicate', $company);
            }

            $company->update(Arr::only($data, $fields));

            return $this->result('updated', $company);
        });
    }

    /**
     * Returns all versions for a company identified by edrpou.
     * Throws ModelNotFoundException (→ 404) if edrpou does not exist.
     */
    public function getVersions(string $edrpou): array
    {
        $company = Company::where('edrpou', $edrpou)->firstOrFail();

        $versions = $company->versions()
            ->orderBy('version')
            ->get(['id', 'version', 'name', 'edrpou', 'address', 'created_at']);

        return [
            'company_id' => $company->id,
            'edrpou'     => $company->edrpou,
            'versions'   => $versions,
        ];
    }

    /**
     * Normalizes field values for reliable comparison:
     * casts all values to string and trims whitespace.
     */
    private function normalizeFields(array $fields): array
    {
        return array_map(fn ($v) => trim((string) $v), $fields);
    }

    private function result(string $status, Company $company): array
    {
        return [
            'status'     => $status,
            'company_id' => $company->id,
            'version'    => $company->getCurrentVersion(),
        ];
    }
}
