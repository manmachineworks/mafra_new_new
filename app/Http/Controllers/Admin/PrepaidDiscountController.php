<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrepaidDiscount;
use Illuminate\Http\Request;

class PrepaidDiscountController extends Controller
{
    public function index()
    {
        $rules = PrepaidDiscount::orderBy('priority')->orderByDesc('percent')->paginate(20);
        return view('backend.marketing.prepaid_discounts.index', compact('rules'));
    }

    public function create()
    {
        return view('backend.marketing.prepaid_discounts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        PrepaidDiscount::create($data);

        flash(translate('Prepaid discount rule created'))->success();
        return redirect()->route('admin.prepaid-discounts.index');
    }

    public function edit(PrepaidDiscount $prepaid_discount)
    {
        return view('backend.marketing.prepaid_discounts.edit', compact('prepaid_discount'));
    }

    public function update(Request $request, PrepaidDiscount $prepaid_discount)
    {
        $data = $this->validated($request);
        $prepaid_discount->update($data);

        flash(translate('Prepaid discount rule updated'))->success();
        return redirect()->route('admin.prepaid-discounts.index');
    }

    public function destroy(PrepaidDiscount $prepaid_discount)
    {
        $prepaid_discount->delete();
        flash(translate('Prepaid discount rule deleted'))->success();
        return back();
    }

    public function toggleStatus(PrepaidDiscount $prepaid_discount)
    {
        $prepaid_discount->update(['is_active' => !$prepaid_discount->is_active]);
        flash(translate('Prepaid discount status updated'))->success();
        return back();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
