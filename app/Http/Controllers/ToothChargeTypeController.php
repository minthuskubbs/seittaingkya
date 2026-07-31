<?php

namespace App\Http\Controllers;

use App\Models\ToothChargeType;
use Illuminate\Http\Request;

class ToothChargeTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin');
    }

    private function kind(Request $request): string
    {
        $kind = $request->input('kind', 'extraction');

        return array_key_exists($kind, ToothChargeType::KINDS) ? $kind : 'extraction';
    }

    public function index(Request $request)
    {
        $kind = $this->kind($request);
        $types = ToothChargeType::kind($kind)->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        return view('tooth_charge_types.index', compact('types', 'kind'));
    }

    public function create(Request $request)
    {
        return view('tooth_charge_types.create', ['kind' => $this->kind($request)]);
    }

    public function store(Request $request)
    {
        ToothChargeType::create($this->validated($request));

        return redirect()->route('tooth-charge-types.index', ['kind' => $request->input('kind')])
            ->with('status', 'Type created.');
    }

    public function edit(ToothChargeType $toothChargeType)
    {
        return view('tooth_charge_types.edit', ['type' => $toothChargeType]);
    }

    public function update(Request $request, ToothChargeType $toothChargeType)
    {
        $toothChargeType->update($this->validated($request));

        return redirect()->route('tooth-charge-types.index', ['kind' => $toothChargeType->kind])
            ->with('status', 'Type updated.');
    }

    public function destroy(ToothChargeType $toothChargeType)
    {
        $kind = $toothChargeType->kind;
        $toothChargeType->delete();

        return redirect()->route('tooth-charge-types.index', ['kind' => $kind])->with('status', 'Type deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'kind' => 'required|in:extraction,implant',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['price'] = $data['price'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
