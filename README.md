# Company Versioning API

REST API for storing and versioning company data.
Every change to the `name` or `address` fields automatically creates a new snapshot (version).

## Stack

| Component | Version  |
|-----------|----------|
| PHP       | 8.4      |
| Laravel   | 12       |
| MySQL     | 8.0      |
| Nginx     | alpine   |
| Docker    | compose  |

---

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) with Docker Compose
- On Windows: WSL2 must be enabled in Docker Desktop settings

## Deployment

### 1. Clone the repository

```bash
git clone <repo-url>
cd company-versioning-api
```

### 2. Create `.env`

```bash
cp .env.example .env
```

### 3. Start containers

```bash
docker compose up -d
```

On first start Docker automatically:
- generates `APP_KEY`
- runs `php artisan migrate` (creates tables)
- fixes storage directory permissions

The service is available at **http://localhost:8080**.

### 4. Seed the database with sample data (optional)

```bash
docker compose exec app php artisan db:seed
```

---

## Tests

```bash
docker compose exec app php artisan test
```

```
Tests: 26 passed (81 assertions)
```

**Feature tests** (`CompanyApiTest`) — 22 tests:
- `POST /api/company` — created / updated / duplicate logic
- Sequential version increment across multiple updates
- Whitespace trimming does not trigger false updates
- Version snapshot stores correct data in `company_versions`
- Validation: all required fields, min/max boundaries, digits-only edrpou
- `GET /api/company/{edrpou}/versions` — returns ordered versions, 404 for unknown/invalid edrpou

**Unit tests** (`HasVersionsTest`) — 4 tests:
- `HasVersions` trait convention: table name, foreign key, versionable fields
- Throws `LogicException` when `$fillable` is empty

---

## API

Full specification — [`openapi.yaml`](openapi.yaml)
(import into Postman: **Import → OpenAPI**)

### POST /api/company

Create or update a company by `edrpou`.

**Request**
```json
{
  "name":    "Acme Corporation",
  "edrpou":  "37027819",
  "address": "123 Main St, New York, NY 10001"
}
```

**Responses**

| HTTP | `status`    | Description                              |
|------|-------------|------------------------------------------|
| 201  | `created`   | Company created (version 1)              |
| 200  | `updated`   | Data changed, version incremented        |
| 200  | `duplicate` | Data unchanged, same version returned    |
| 422  |             | Validation error                         |

```json
{ "status": "created", "company_id": 1, "version": 1 }
```

### GET /api/company/{edrpou}/versions

Retrieve all versions of a company.

| HTTP | Description                                     |
|------|-------------------------------------------------|
| 200  | Array of versions                               |
| 404  | Company not found or edrpou is invalid          |

```json
{
  "company_id": 1,
  "edrpou": "37027819",
  "versions": [
    {
      "id": 1,
      "version": 1,
      "name": "Acme Corporation",
      "edrpou": "37027819",
      "address": "123 Main St, New York, NY 10001",
      "created_at": "2026-03-09T10:00:00.000000Z"
    }
  ]
}
```

---

## Field Validation

| Field     | Rules                                  |
|-----------|----------------------------------------|
| `name`    | required, string, min:2, max:256       |
| `edrpou`  | required, digits only, 1–10 digits     |
| `address` | required, string, max:1000             |

---

## Versioning Architecture

Versioning logic is encapsulated in the `HasVersions` trait and is not tied to the `Company` model.
To enable versioning on any model:

```php
use App\Traits\HasVersions;

class MyModel extends Model
{
    use HasVersions;

    protected $fillable = ['field1', 'field2'];
}
```

The trait automatically:
- creates a version snapshot on `created`
- creates a new version on `updated` (only when `$fillable` fields change)
- is protected against race conditions via `DB::transaction()` + `lockForUpdate()`
