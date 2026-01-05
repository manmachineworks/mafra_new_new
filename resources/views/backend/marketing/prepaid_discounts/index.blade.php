@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ translate('Prepaid Discounts') }}</h5>
                <a href="{{ route('admin.prepaid-discounts.create') }}" class="btn btn-primary btn-sm">{{ translate('Add Rule') }}</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 aiz-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Title') }}</th>
                            <th>{{ translate('Range') }}</th>
                            <th>{{ translate('Percent') }}</th>
                            <th>{{ translate('Priority') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th class="text-right">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr>
                                <td>{{ $rule->title }}</td>
                                <td>₹{{ number_format($rule->min_amount, 2) }} - {{ $rule->max_amount !== null ? '₹'.number_format($rule->max_amount, 2) : translate('No Limit') }}</td>
                                <td>{{ rtrim(rtrim($rule->percent, '0'), '.') }}%</td>
                                <td>{{ $rule->priority }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.prepaid-discounts.toggle', $rule) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                            {{ $rule->is_active ? translate('Active') : translate('Disabled') }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.prepaid-discounts.edit', $rule) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Edit') }}">
                                        <i class="las la-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.prepaid-discounts.destroy', $rule) }}" method="POST" class="d-inline-block" onsubmit="return confirm('{{ translate('Delete this rule?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="{{ translate('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-4">{{ translate('No rules found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $rules->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
