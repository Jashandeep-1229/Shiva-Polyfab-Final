<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@php
$website_setting = App\Models\WebsiteSetting::find('1');
@endphp
@if (trim($slot) === 'Laravel')

<img src="{{asset('uploads/'.$website_setting->logo)}}" style="width:100% !important" class="logo" alt="AigPunjab Logo">
@else
<img src="{{asset('uploads/'.$website_setting->logo)}}" style="width:100% !important" class="logo" alt="AigPunjab Logo">
@endif
</a>
</td>
</tr>