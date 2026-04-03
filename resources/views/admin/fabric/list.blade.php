@foreach($fabric as $item)
<option value="{{ $item->id }}" @selected($loop->last)>{{ $item->name }}</option>
@endforeach