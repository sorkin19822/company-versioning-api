<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {}

    /**
     * POST /api/company
     */
    public function upsert(CompanyRequest $request): JsonResponse
    {
        $result = $this->companyService->upsert($request->validated());

        $status = $result['status'] === 'created' ? 201 : 200;

        return response()->json($result, $status);
    }

    /**
     * GET /api/company/{edrpou}/versions
     */
    public function versions(string $edrpou): JsonResponse
    {
        return response()->json(
            $this->companyService->getVersions($edrpou)
        );
    }
}
