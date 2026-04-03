@foreach($agents as $item)
<option value="{{ $item->id }}" data-sale-executive-id="{{ $item->sale_executive_id }}" @selected($loop->last)>{{ $item->name }} ({{ $item->phone_no ?? '' }})</option>
@endforeach
