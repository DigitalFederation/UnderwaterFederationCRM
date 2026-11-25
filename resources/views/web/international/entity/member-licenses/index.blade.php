@section('title', __('admin.member_international_licenses'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ __('admin.international_licenses') }}</h2>
                <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('admin.official_international_licenses') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.member_international_licenses') }}</h1>
                <p class="text-slate-600 dark:text-slate-400">{{ $entity->name }}</p>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('international.entity.license-purchase.index', ['type' => 'members']) }}" class="btn btn-primary">
                    {{ __('admin.purchase_license') }}
                </a>
            </div>
        </div>

        @if($licenses->count() > 0)
            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">
                <x-dynamic-table
                    :headers="[
                        __('admin.license'),
                        __('admin.holder'),
                        __('admin.committee'),
                        __('admin.federation'),
                        __('admin.start_date'),
                        __('admin.expiry_date'),
                        __('admin.status'),
                    ]">
                    @foreach($licenses as $license)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->license_name }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->owner?->name ?? '-' }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->license?->committee?->translated_name ?? '-' }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->federation?->name ?? '-' }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->current_term_starts_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->current_term_ends_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.badge :status="ucwords($license->stateName())" :color="$license->stateColor()" />
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>

            <div class="mt-8">
                {{ $licenses->links() }}
            </div>
        @else
            <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-8 text-center">
                <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">{{ __('admin.no_licenses') }}</h3>
            </div>
        @endif

    </div>
</x-layout>
