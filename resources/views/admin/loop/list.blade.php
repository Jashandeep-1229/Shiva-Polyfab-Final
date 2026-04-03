@foreach($loop_colors as $item)
<option value="{{ $item->name }}" @if($loop->last) selected @endif>{{ $item->name }}</option>
@endforeach
