#!/bin/bash
cd "$(dirname "$0")"
cd ../../../
php module/Websocket/server/GWS_ServerMain.php
