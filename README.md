# gdo6-websocket
Websocket module for gdo6.


### Installation
You have to clone a slightly patched version of Ratchet.

    cd gdo6/GDO && git clone https://github.com/gizmore/gdo6-websocket Websocket
    cd gdo6/GDO/Websocket && git clone https://github.com/gizmore/gwf4-ratchet # No typo

### Runtime
Sadly, Ratchet does not support TLS yet.
Because of this it is currently recommended to install nginx as a proxy.

### 

### Protocol
The gdo6 websocket protocol is binary