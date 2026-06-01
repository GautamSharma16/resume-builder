<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AffindaResumeParserService
{
    private ?string $resolvedOrganization = null;

    private ?string $resolvedWorkspace = null;

    private ?string $resolvedDocumentType = null;

    /**
     * @return array{success:bool,data?:array,message?:string,meta?:array}
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $apiKey = trim((string) config('services.affinda.key', ''));
        if ($apiKey === '') {
            return ['success' => false, 'message' => 'Affinda API key is not configured.'];
        }

        $organization = $this->organizationId();
        if ($organization === '') {
            return ['success' => false, 'message' => 'Affinda organization could not be resolved. Set AFFINDA_ORGANIZATION in .env or verify your API key.'];
        }

        $workspace = $this->workspaceId($organization);
        if ($workspace === '') {
            return ['success' => false, 'message' => 'Affinda workspace could not be resolved. Create a workspace in the Affinda app or set AFFINDA_WORKSPACE in .env.'];
        }

        $documentType = $this->documentTypeId($organization, $workspace);

        $baseUrl = rtrim((string) config('services.affinda.base_url', 'https://api.affinda.com/v3'), '/');
        $path    = $file->getRealPath() ?: '';

        if ($path === '' || ! is_readable($path)) {
            return ['success' => false, 'message' => 'Uploaded file is not readable.'];
        }

        try {
            $response = $this->uploadDocument($apiKey, $baseUrl, $path, $file->getClientOriginalName(), $workspace, $documentType);

            if (! $response->successful() && $documentType !== '') {
                $detail = strtolower((string) ($response->json('errors.0.detail') ?? ''));
                if ($response->status() === 400 && str_contains($detail, 'not part of workspace')) {
                    Log::info('Affinda document type not in workspace; retrying without documentType', [
                        'document_type' => $documentType,
                        'workspace'     => $workspace,
                    ]);
                    $documentType = '';
                    $response     = $this->uploadDocument($apiKey, $baseUrl, $path, $file->getClientOriginalName(), $workspace, '');
                }
            }

            if (! $response->successful()) {
                $message = $this->formatAffindaError($response);

                Log::warning('Affinda parse failed', ['status' => $response->status(), 'body' => $response->json()]);

                return ['success' => false, 'message' => $message];
            }

            $json = $response->json();
            if (($json['meta']['failed'] ?? false) === true) {
                $message = (string) ($json['meta']['errorDetail'] ?? $json['error']['errorDetail'] ?? 'Affinda could not parse this file.');

                return ['success' => false, 'message' => $message];
            }

            $payload = $json['data'] ?? [];
            $data    = is_array($payload['resume'] ?? null) ? $payload['resume'] : (is_array($payload) ? $payload : []);

            if (! is_array($data) || empty($data)) {
                return ['success' => false, 'message' => 'Affinda returned an empty parse result.'];
            }

            return [
                'success' => true,
                'data'    => $data,
                'meta'    => [
                    'identifier'     => $json['meta']['identifier'] ?? null,
                    'organization'   => $organization,
                    'workspace'      => $workspace,
                    'document_type'  => $documentType,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Affinda exception: '.$e->getMessage());

            return ['success' => false, 'message' => 'Affinda connection error: '.$e->getMessage()];
        }
    }

    private function organizationId(): string
    {
        $configured = trim((string) config('services.affinda.organization', ''));
        if ($configured !== '') {
            return $configured;
        }

        if ($this->resolvedOrganization !== null) {
            return $this->resolvedOrganization;
        }

        $this->resolvedOrganization = $this->fetchFirstOrganizationId();

        return $this->resolvedOrganization;
    }

    private function workspaceId(string $organization): string
    {
        $configured = trim((string) config('services.affinda.workspace', ''));
        if ($configured !== '') {
            return $configured;
        }

        if ($this->resolvedWorkspace !== null) {
            return $this->resolvedWorkspace;
        }

        $this->resolvedWorkspace = $this->fetchFirstWorkspaceId($organization);
        if ($this->resolvedWorkspace === '') {
            $this->resolvedWorkspace = $this->createWorkspace($organization);
        }

        return $this->resolvedWorkspace;
    }

    private function documentTypeId(string $organization, string $workspace): string
    {
        if ($this->resolvedDocumentType !== null) {
            return $this->resolvedDocumentType;
        }

        $candidates = array_values(array_filter([
            trim((string) config('services.affinda.document_type', '')),
            $this->fetchResumeDocumentTypeId($organization, $workspace),
        ]));

        foreach ($candidates as $candidate) {
            if ($this->workspaceAcceptsDocumentType($workspace, $candidate)) {
                $this->resolvedDocumentType = $candidate;

                return $this->resolvedDocumentType;
            }
        }

        $this->resolvedDocumentType = '';

        return '';
    }

    private function workspaceAcceptsDocumentType(string $workspace, string $documentType): bool
    {
        if ($documentType === '') {
            return false;
        }

        $apiKey  = trim((string) config('services.affinda.key', ''));
        $baseUrl = rtrim((string) config('services.affinda.base_url', 'https://api.affinda.com/v3'), '/');

        try {
            $response = Http::withToken($apiKey)->timeout(30)->get("{$baseUrl}/workspaces/{$workspace}");
            if (! $response->successful()) {
                return false;
            }

            $detail = $response->json();
            if (! is_array($detail)) {
                return false;
            }

            foreach (['documentTypes', 'collections'] as $key) {
                $items = $detail[$key] ?? [];
                if (! is_array($items)) {
                    continue;
                }
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $id = trim((string) ($item['identifier'] ?? $item['id'] ?? ''));
                    if ($id === $documentType) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Affinda workspace lookup failed: '.$e->getMessage());
        }

        return false;
    }

    /**
     * @return \Illuminate\Http\Client\Response
     */
    private function uploadDocument(
        string $apiKey,
        string $baseUrl,
        string $path,
        string $filename,
        string $workspace,
        string $documentType
    ) {
        $payload = [
            'workspace' => $workspace,
            'wait'      => 'true',
            'compact'   => 'true',
        ];

        if ($documentType !== '') {
            $payload['documentType'] = $documentType;
        }

        return Http::withToken($apiKey)
            ->timeout((int) config('services.affinda.timeout', 120))
            ->attach('file', file_get_contents($path), $filename)
            ->post("{$baseUrl}/documents", $payload);
    }

    private function formatAffindaError(\Illuminate\Http\Client\Response $response): string
    {
        $errors = $response->json('errors');
        if (is_array($errors) && isset($errors[0]) && is_array($errors[0])) {
            $detail = trim((string) ($errors[0]['detail'] ?? $errors[0]['message'] ?? ''));
            if ($detail !== '') {
                return 'Affinda: '.$detail;
            }
        }

        return (string) (
            $response->json('message')
            ?? $response->json('error.message')
            ?? ('Affinda API error HTTP '.$response->status())
        );
    }

    private function fetchFirstOrganizationId(): string
    {
        $apiKey  = trim((string) config('services.affinda.key', ''));
        $baseUrl = rtrim((string) config('services.affinda.base_url', 'https://api.affinda.com/v3'), '/');

        try {
            $response = Http::withToken($apiKey)->timeout(30)->get("{$baseUrl}/organizations");

            if (! $response->successful()) {
                return '';
            }

            $results = $response->json();
            if (! is_array($results)) {
                return '';
            }

            foreach ($results as $organization) {
                if (! is_array($organization)) {
                    continue;
                }
                $id = trim((string) ($organization['identifier'] ?? $organization['id'] ?? ''));
                if ($id !== '') {
                    return $id;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Affinda organization list failed: '.$e->getMessage());
        }

        return '';
    }

    private function fetchFirstWorkspaceId(string $organization): string
    {
        $apiKey  = trim((string) config('services.affinda.key', ''));
        $baseUrl = rtrim((string) config('services.affinda.base_url', 'https://api.affinda.com/v3'), '/');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->get("{$baseUrl}/workspaces", ['organization' => $organization]);

            if (! $response->successful()) {
                Log::warning('Affinda workspace list failed', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);

                return '';
            }

            $results = $this->normalizeListPayload($response->json());

            foreach ($results as $workspace) {
                if (! is_array($workspace)) {
                    continue;
                }
                $id = trim((string) ($workspace['identifier'] ?? $workspace['id'] ?? ''));
                if ($id !== '') {
                    return $id;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Affinda workspace list failed: '.$e->getMessage());
        }

        return '';
    }

    private function createWorkspace(string $organization): string
    {
        $apiKey  = trim((string) config('services.affinda.key', ''));
        $baseUrl = rtrim((string) config('services.affinda.base_url', 'https://api.affinda.com/v3'), '/');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post("{$baseUrl}/workspaces", [
                    'organization' => $organization,
                    'name'         => 'CVBliss Resume Parser',
                ]);

            if (! $response->successful()) {
                Log::warning('Affinda workspace create failed', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);

                return '';
            }

            $workspace = $response->json();

            return trim((string) (is_array($workspace) ? ($workspace['identifier'] ?? $workspace['id'] ?? '') : ''));
        } catch (\Throwable $e) {
            Log::warning('Affinda workspace create failed: '.$e->getMessage());
        }

        return '';
    }

    private function fetchResumeDocumentTypeId(string $organization, string $workspace): string
    {
        $apiKey  = trim((string) config('services.affinda.key', ''));
        $baseUrl = rtrim((string) config('services.affinda.base_url', 'https://api.affinda.com/v3'), '/');

        foreach ([
            ['workspace' => $workspace],
            ['organization' => $organization],
        ] as $query) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout(30)
                    ->get("{$baseUrl}/document_types", $query);

                if (! $response->successful()) {
                    continue;
                }

                $results = $this->normalizeListPayload($response->json());

                foreach ($results as $type) {
                    if (! is_array($type)) {
                        continue;
                    }
                    $name = strtolower((string) ($type['name'] ?? $type['label'] ?? ''));
                    $id   = trim((string) ($type['identifier'] ?? $type['id'] ?? ''));
                    if ($id !== '' && (str_contains($name, 'resume') || str_contains($name, 'cv'))) {
                        return $id;
                    }
                }

                foreach ($results as $type) {
                    if (! is_array($type)) {
                        continue;
                    }
                    $id = trim((string) ($type['identifier'] ?? $type['id'] ?? ''));
                    if ($id !== '') {
                        return $id;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Affinda document type list failed: '.$e->getMessage());
            }
        }

        return '';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeListPayload(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (isset($payload['results']) && is_array($payload['results'])) {
            return $payload['results'];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        if ($payload !== [] && array_is_list($payload)) {
            return $payload;
        }

        return [];
    }
}
