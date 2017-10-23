@section('host')

    charset utf8;

    server_name {{$SERVER_NAME}}@foreach($ALIASES as $ALIAS) {{$ALIAS}}@endforeach;
    root {{$DOCUMENT_ROOT}};

    add_header Access-Control-Allow-Origin "{{$SERVER_NAME}}";
    @foreach($ALIASES as $ALIAS)add_header Access-Control-Allow-Origin "{{$ALIAS}}";
    @endforeach

    index index.php index.html;

    location / {
        try_files $uri $uri/ @rewrites;
    }

    location ~* /storage/.*.php$ {
        return 444;
    }

    location @rewrites {
        rewrite ^/(.*)/$ /$1 permanent;
        rewrite (.*) /index.php last;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~ .php$ {
            try_files $uri =404;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
    }

    location ~ /\.well-known-2 {
        root /var/www/html;
    }
@endsection


server {

    listen 80;
    listen [::]:80;

    @yield('host')
}
@if(isset($SSL['CRT']) && isset($SSL['KEY']) && isset($SSL['PEM']))
server {

    listen          443 ssl http2;
    listen          [::]:443;

    ssl on;
    ssl_session_timeout 24h;
    ssl_session_cache shared:SSL:2m;

    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers kEECDH+AES128:kEECDH:kEDH:-3DES:kRSA+AES128:kEDH+3DES:DES-CBC3-SHA:!RC4:!aNULL:!eNULL:!MD5:!EXPORT:!LOW:!SEED:!CAMELLIA:!IDEA:!PSK:!SRP:!SSLv2;

    ssl_dhparam /etc/nginx/ssl/dhparam.pem;
    ssl_prefer_server_ciphers on;

    ssl_certificate         {{$SSL['PEM']}};
    ssl_certificate_key     {{$SSL['KEY']}};

    @yield('host')
}
@endif