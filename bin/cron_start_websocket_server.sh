#!/bin/bash
cd "$(dirname "$0")"

# This script starts the websocket server every 3 seconds for about a minute.
# Call this in a cronjob.

for (( i=0 ; i<20; i++ ))
do
	./start_websocket_server.sh
	res=$?
	if [ $res -eq 0 ]
	then
		exit 0
	fi
	if [ $res -eq 127 ]
	then
		exit 127
	fi
	if [ $res -eq 130 ]
	then
		exit 130
	fi
	sleep 3
done
