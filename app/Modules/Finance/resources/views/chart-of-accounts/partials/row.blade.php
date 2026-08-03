<tr class="border-b border-border-gray hover:bg-mist-gray">
    <td class="px-4 py-3 tabular-nums" style="padding-left: {{ 16 + $depth * 24 }}px">{{ $account->code }}</td>
    <td class="px-4 py-3">{{ $account->name }}</td>
    <td class="px-4 py-3 text-body-sm text-slate-gray">{{ ucfirst($account->account_type) }}</td>
    <td class="px-4 py-3">
        @if ($account->is_postable)
            <x-badge status="success">Postable</x-badge>
        @else
            <x-badge status="info">Header</x-badge>
        @endif
    </td>
</tr>
@foreach ($account->children as $child)
    @include('finance::chart-of-accounts.partials.row', ['account' => $child, 'depth' => $depth + 1])
@endforeach
