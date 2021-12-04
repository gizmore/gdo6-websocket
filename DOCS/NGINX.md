# gdo6 websocket via nginx proxy.

## Install acme.sh
Install acme.sh for LetsEncrypt
Create a certificate with acme.sh

    acme.sh --issue --domain foo.com --apache --writable /home/user/www/gdo6
    
    
### Create PEM formats

not required anymore?

    # Create PFX
    openssl pkcs12 -export -out gizmore.org.pfx -inkey gizmore.org.key -in fullchain.cer -nodes
    # Create chain PEM
    openssl pkcs12 -in gizmore.org.pfx -out gizmore.org.public.pem -nodes -nokeys
    openssl pkcs12 -in gizmore.org.pfx -out gizmore.org.private.pem -nodes
    
    
## Install nginx

Add a TLS proxy site.
In this example we offer wss://gizmore.org:61222 and proxy to gizmore:org:61221.

    root@h1111111:~# cat /etc/nginx/sites-enabled/gizmore_wss
    
    upstream websocketserver {
        server gizmore.org:61221;
    }

    server {
        server_name gizmore.org;
        listen 61222;
        ssl on;
        ssl_certificate /root/.acme.sh/gizmore.org/gizmore.org.public.pem;
        ssl_certificate_key /root/.acme.sh/gizmore.org/gizmore.org.key;

        access_log /var/log/giz-wss-access-ssl.log;
        error_log /var/log/giz-wss-error-ssl.log;

        location / {
                proxy_pass http://websocketserver;
                proxy_http_version 1.1;
                proxy_set_header Upgrade $http_upgrade;
                proxy_set_header Connection "upgrade";
                proxy_set_header Host $host;

                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_set_header X-Forwarded-Proto https;
                proxy_read_timeout 86400; # neccessary to avoid websocket timeou                                                                                                                                                             t disconnect
                proxy_redirect off;
        }
    }

## Configure gdo6

Configure the Websocket module in gdo6.
Set the ws_url to wss://gizmore.org:61222
Set the ws_port to 61221
