<h2 class="block-title">{{ $class }}</h2>
<table class="table table-bordered">
    <colgroup>
        <col width="250px">
        <col width="30px">
        <col width="50px">
        <col width="100px">
        <col width="150px">
        <col width="200px">
        <col width="100px">
        <col width="200px">
    </colgroup>
    <tr>
        <th>Endpoint</th>
        <th title="Caching (sec)">C</th>
        <th>&nbsp;</th>
        <th title="Middlewares">Mid</th>
        <th>With</th>
        <th>Arguments</th>
        <th>Parameters</th>
        <th>Link</th>
        <th>Description</th>
    </tr>
    @foreach ( $routes as $route )
        <tr>
            <td style="word-break: break-all;"><a href="/api{{ $route['example'] ?: $route['url'] }}?pretty=true" target="run:{{ $route['urn'] }}">/api{{ $route['url'] }}</a></td>
            <td>
                {{ $route['cache'][0] ? $route['cache'][0] : '-' }}
            </td>
            <td>
                {{ implode(', ', $route['method']) }}
            </td>
            <td>
                @foreach ( $route['middleware'] as $middleware )
                    <div>{{ $middleware }}</div>
                @endforeach
            </td>
            <td>
                @foreach ( $route['with'] as $with )
                    <div>{{ $with }}</div>
                @endforeach
            </td>
            <td>
                @foreach ( $route['args'] as $arg )
                    <div><nobr>{{ $arg }}</nobr></div>
                @endforeach
            </td>
            <td>
                @foreach ( $route['params'] as $param )
                    <div><nobr>{{ implode(' ', $param) }}</nobr></div>
                @endforeach
            </td>
            <td>
                @foreach ( $route['link'] as $link )
                    <div><a href="{{ $link[0] }}" target="wiki:{{ $route['urn'] }}">{{ $link[1] ?: $link[0] }}</a></div>
                @endforeach
            </td>
            <td>{!! $route['title'] ?: $route['description'] !!}</td>
        </tr>
    @endforeach
</table>
