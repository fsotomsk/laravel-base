@section('host')

    ServerName {{$SERVER_NAME}}
    @foreach($ALIASES as $ALIAS)ServerAlias {{$ALIAS}}@endforeach;

    ServerAdmin {{$ADMIN_EMAIL}}
    DocumentRoot "{{$DOCUMENT_ROOT}}"
    DirectoryIndex index.php index.html

    <Directory "{{$DOCUMENT_ROOT}}">
        Allow from all
        Options -Indexes
        AllowOverride all
        Require all granted
        php_flag engine On
    </Directory>
@endsection

<VirtualHost *:80>
    @yield('host')
</VirtualHost>

