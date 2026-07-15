<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Receives a batch of operations queued by the PWA while offline and replays
 * them once connectivity returns. Idempotency is guaranteed by client_uuid:
 * replaying the same operation twice will not create duplicate rows.
 */
class SyncController extends Controller
{
    public function push(Request $request)
    {
        $operations = $request->validate([
            'operations' => 'required|array',
            'operations.*.entity' => 'required|in:patient',
            'operations.*.action' => 'required|in:create,update',
            'operations.*.uuid' => 'required|uuid',
            'operations.*.payload' => 'required|array',
        ])['operations'];

        $results = [];

        foreach ($operations as $op) {
            $results[$op['uuid']] = DB::transaction(function () use ($op) {
                return match ($op['entity']) {
                    'patient' => $this->syncPatient($op),
                    default => ['status' => 'ignored'],
                };
            });
        }

        return response()->json(['results' => $results]);
    }

    private function syncPatient(array $op): array
    {
        $payload = $op['payload'];
        $payload['client_uuid'] = $op['uuid'];

        // Already synced? Return existing id (idempotent replay).
        $existing = Patient::where('client_uuid', $op['uuid'])->first();
        if ($existing) {
            if ($op['action'] === 'update') {
                $existing->update($this->patientFields($payload));
            }

            return ['status' => 'synced', 'id' => $existing->id];
        }

        $patient = Patient::create($this->patientFields($payload) + [
            'client_uuid' => $op['uuid'],
        ]);

        return ['status' => 'created', 'id' => $patient->id];
    }

    private function patientFields(array $payload): array
    {
        return collect($payload)->only([
            'name', 'age', 'address', 'phone', 'doctor_desc', 'assistance_desc',
            'assigned_doctor_id', 'medical_condition', 'drug_allergy', 'diabetes',
        ])->toArray();
    }
}
