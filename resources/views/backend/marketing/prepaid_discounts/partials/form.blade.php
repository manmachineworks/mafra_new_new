<div class="form-group">
    <label class="form-label">{{ translate('Title') }}</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $prepaid_discount->title ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label">{{ translate('Min Amount (₹)') }}</label>
            <input type="number" step="0.01" min="0" name="min_amount" class="form-control" value="{{ old('min_amount', $prepaid_discount->min_amount ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label">{{ translate('Max Amount (₹)') }}</label>
            <input type="number" step="0.01" min="0" name="max_amount" class="form-control" value="{{ old('max_amount', $prepaid_discount->max_amount ?? '') }}">
            <small class="text-muted">{{ translate('Leave blank for no upper limit') }}</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">{{ translate('Percent (%)') }}</label>
            <input type="number" step="0.01" min="0" max="100" name="percent" class="form-control" value="{{ old('percent', $prepaid_discount->percent ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">{{ translate('Priority') }}</label>
            <input type="number" min="1" name="priority" class="form-control" value="{{ old('priority', $prepaid_discount->priority ?? 10) }}" required>
            <small class="text-muted">{{ translate('Lower number = higher priority') }}</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">{{ translate('Status') }}</label>
            <select name="is_active" class="form-control">
                <option value="1" @selected(old('is_active', $prepaid_discount->is_active ?? 1) == 1)>{{ translate('Active') }}</option>
                <option value="0" @selected(old('is_active', $prepaid_discount->is_active ?? 1) == 0)>{{ translate('Disabled') }}</option>
            </select>
        </div>
    </div>
</div>
