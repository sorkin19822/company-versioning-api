<?php

namespace App\Services;

use App\Models\Company;
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
                $company = Company::create($data);

                return $this->result('created', $company);
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
