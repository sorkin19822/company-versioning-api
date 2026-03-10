<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Versioning API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 48px 24px;
        }

        .container { max-width: 860px; margin: 0 auto; }

        header { margin-bottom: 48px; }

        header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 8px;
        }

        header p { color: #94a3b8; font-size: 1rem; }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 6px;
        }
        .badge-green  { background: #14532d; color: #86efac; }
        .badge-blue   { background: #1e3a5f; color: #93c5fd; }
        .badge-purple { background: #3b0764; color: #d8b4fe; }

        section { margin-bottom: 40px; }

        h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 16px;
        }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 12px;
        }

        .endpoint {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .method {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            min-width: 52px;
            text-align: center;
        }
        .method-post { background: #422006; color: #fb923c; }
        .method-get  { background: #14532d; color: #4ade80; }

        .url {
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: #f1f5f9;
        }

        .desc { color: #94a3b8; font-size: 0.9rem; margin-bottom: 16px; }

        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th { text-align: left; color: #64748b; font-weight: 500; padding: 6px 12px; border-bottom: 1px solid #334155; }
        td { padding: 8px 12px; border-bottom: 1px solid #1e293b; color: #cbd5e1; }
        td:first-child { font-family: monospace; color: #93c5fd; }

        pre {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 16px;
            font-size: 0.82rem;
            color: #7dd3fc;
            overflow-x: auto;
            margin-top: 12px;
            line-height: 1.6;
        }

        .status-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px; }

        .status-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.82rem;
        }

        .http-code { font-weight: 700; font-family: monospace; }
        .code-201 { color: #4ade80; }
        .code-200 { color: #60a5fa; }
        .code-422 { color: #f87171; }
        .code-404 { color: #f87171; }

        .step {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
            align-items: flex-start;
        }

        .step-num {
            min-width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #334155;
            color: #f1f5f9;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step-body { flex: 1; }
        .step-body strong { color: #f1f5f9; display: block; margin-bottom: 4px; }
        .step-body span { color: #94a3b8; font-size: 0.875rem; }

        code {
            font-family: monospace;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.82rem;
            color: #7dd3fc;
        }

        footer {
            margin-top: 60px;
            padding-top: 24px;
            border-top: 1px solid #1e293b;
            color: #475569;
            font-size: 0.8rem;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
    </style>
</head>
<body>
<div class="container">

    <header>
        <h1>Company Versioning API</h1>
        <p style="margin-bottom:12px">REST API for storing and versioning company data</p>
        <span class="badge badge-blue">Laravel 12</span>
        <span class="badge badge-purple">PHP 8.4</span>
        <span class="badge badge-green">MySQL 8.0</span>
    </header>

    <section>
        <h2>Endpoints</h2>

        <div class="card">
            <div class="endpoint">
                <span class="method method-post">POST</span>
                <span class="url">/api/company</span>
            </div>
            <p class="desc">Create or update a company by EDRPOU code. Automatically versions every data change.</p>

            <table>
                <tr><th>Field</th><th>Type</th><th>Rules</th></tr>
                <tr><td>name</td><td>string</td><td>required, min:2, max:256</td></tr>
                <tr><td>edrpou</td><td>string</td><td>required, digits only, 1–10 chars</td></tr>
                <tr><td>address</td><td>string</td><td>required, max:1000</td></tr>
            </table>

            <div class="status-row">
                <div class="status-pill"><span class="http-code code-201">201</span><span>created — new company, version 1</span></div>
                <div class="status-pill"><span class="http-code code-200">200</span><span>updated — data changed, new version</span></div>
                <div class="status-pill"><span class="http-code code-200">200</span><span>duplicate — data unchanged</span></div>
                <div class="status-pill"><span class="http-code code-422">422</span><span>validation error</span></div>
            </div>

            <pre>// Response
{ "status": "created", "company_id": 1, "version": 1 }</pre>
        </div>

        <div class="card">
            <div class="endpoint">
                <span class="method method-get">GET</span>
                <span class="url">/api/company/{edrpou}/versions</span>
            </div>
            <p class="desc">Returns all saved versions of a company in chronological order.</p>

            <div class="status-row">
                <div class="status-pill"><span class="http-code code-200">200</span><span>list of versions</span></div>
                <div class="status-pill"><span class="http-code code-404">404</span><span>company not found or invalid edrpou</span></div>
            </div>

            <pre>// Response
{
  "company_id": 1,
  "edrpou": "37027819",
  "versions": [
    { "id": 1, "version": 1, "name": "Acme Corp", "address": "...", "created_at": "..." },
    { "id": 2, "version": 2, "name": "Acme Corp", "address": "new address", "created_at": "..." }
  ]
}</pre>
        </div>
    </section>

    <section>
        <h2>Testing Flow</h2>
        <div class="card">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-body">
                    <strong>Create a company → 201 created, version 1</strong>
                    <span>POST <code>/api/company</code> with name, edrpou, address</span>
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-body">
                    <strong>Send same data again → 200 duplicate</strong>
                    <span>No new version is created. Version number stays at 1.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-body">
                    <strong>Change address or name → 200 updated, version 2</strong>
                    <span>A new snapshot is saved. Version increments automatically.</span>
                </div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-body">
                    <strong>Fetch all versions → 200 with array</strong>
                    <span>GET <code>/api/company/{edrpou}/versions</code> — returns full history.</span>
                </div>
            </div>
            <div class="step" style="margin-bottom:0">
                <div class="step-num">5</div>
                <div class="step-body">
                    <strong>Run automated tests</strong>
                    <span><code>docker compose exec app php artisan test</code> — 26 tests, 81 assertions</span>
                </div>
            </div>
        </div>
    </section>

    <section>
        <h2>Postman / OpenAPI</h2>
        <div class="card">
            <p class="desc" style="margin:0">
                Import <code>openapi.yaml</code> from the repository root into Postman via
                <strong style="color:#f1f5f9">Import → OpenAPI</strong>.
                All requests with examples are ready to use.
            </p>
        </div>
    </section>

    <footer>
        <span>Company Versioning API — PHP Backend Developer Test Assignment</span>
        <span>Laravel 12 · PHP 8.4 · MySQL 8.0 · Docker</span>
    </footer>

</div>
</body>
</html>
