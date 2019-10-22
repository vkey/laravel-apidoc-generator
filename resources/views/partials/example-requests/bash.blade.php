```bash
curl -X {{$route['methods'][0]}} {{$route['methods'][0] == 'GET' ? '-G ' : ''}}"{{ rtrim($baseUrl, '/')}}/{{ ltrim($route['boundUri'], '/') }}@if(count($route['cleanQueryParameters']))?@foreach($route['cleanQueryParameters'] as $parameter => $value)
{{ urlencode($parameter) }}={{ urlencode($value) }}@if(!$loop->last)&@endif
@endforeach
@endif" @if(count($route['headers']))\
@foreach($route['headers'] as $header => $value)
    -H "{{$header}}: {{$value}}"@if(! ($loop->last) || ($loop->last && count($route['bodyParameters']))) \
@endif
@endforeach
@endif
@if(count($route['cleanBodyParameters']))
@if(isset($route['headers']['Content-Type']))
@if(preg_match('#multipart#i', $route['headers']['Content-Type']))
@foreach($route['cleanBodyParameters'] as $field => $value)
    -F "{{$field}}={{$value}}"@if(! ($loop->last) || ($loop->last && count($route['cleanBodyParameters']))) \
@endif
@endforeach
@elseif (preg_match('#application/x-www-form-urlencoded#i', $route['headers']['Content-Type']))
@foreach($route['cleanBodyParameters'] as $field => $value)
    -F "{{$field}}={{$value}}"@if(! ($loop->last) || ($loop->last && count($route['cleanBodyParameters']))) \
@endif
@endforeach
@else
    -d '{!! json_encode($route['cleanBodyParameters']) !!}'
@endif
@else
    -d '{!! json_encode($route['cleanBodyParameters']) !!}'
@endif
@endif

```
