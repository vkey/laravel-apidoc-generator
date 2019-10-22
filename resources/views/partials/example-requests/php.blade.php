```php

$client = new \GuzzleHttp\Client();
$response = $client->{{ strtolower($route['methods'][0]) }}("{{ rtrim($baseUrl, '/') . '/' . ltrim($route['boundUri'], '/') }}", [
@if(!empty($route['headers']))
    'headers' => [
    @foreach($route['headers'] as $header => $value)
        "{{$header}}" => "{{$value}}",
    @endforeach
    ],
@endif
@if(!empty($route['cleanQueryParameters']))
    'query' => [
    @foreach($route['cleanQueryParameters'] as $parameter => $value)
        "{{$parameter}}" => "{{$value}}",
    @endforeach
    ],
@endif
@if(!empty($route['cleanBodyParameters']))
    @php
        if(isset($route['headers']['Content-Type'])){
            if(preg_match('#multipart#i', $route['headers']['Content-Type'])){
                $bodyType = 'multipart';
        }elseif (preg_match('#application/x-www-form-urlencoded#i', $route['headers']['Content-Type'])){
                $bodyType = 'form_params';
        }
        else{
            $bodyType = 'json';
            }
        }
    @endphp
    '{{$bodyType}}' => [
    @foreach($route['cleanBodyParameters'] as $parameter => $value)
        "{{$parameter}}" => "{{$value}}",
    @endforeach
    ],
@endif
]);
$body = $response->getBody();
print_r(json_decode((string) $body));
```
