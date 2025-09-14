#!/bin/bash
#
#	Raspotify shell script used Onevevnt
#	====================================
#
#	used to control GPIO pin 11 which controls
#	the amplifier sleep mode (if appropriate)
#	NB pin 11 is GPIO pin 0
#
#echo Spotify event: ---------------------------------------------
#echo PLAYER_EVENT: $PLAYER_EVENT
#echo TRACK_ID:     $TRACK_ID
#echo OLD_TRACK_ID: $OLD_TRACK_ID
if   [ "$PLAYER_EVENT" = "playing" ]; then
    echo "SPOTIFY GPIO: Turning sleep mode off"
    pigs write 17 0
elif [ "$PLAYER_EVENT" = "paused" ]; then
    echo "SPOTIFY GPIO: Turning sleep mode on"
    pigs write 17 1
elif [ "$PLAYER_EVENT" = "stop" ]; then
    echo "SPOTIFY GPIO: Turning sleep mode on"
    pigs write 17 1
#elif [ "$PLAYER_EVENT" = "start" ]; then
#   no action
#elif [ "$PLAYER_EVENT" = "change" ]; then
#   no action
#elif [ "$PLAYER_EVENT" = "volume_set" ]; then
#   no action
#else
#    echo "Unrecognised player event:", $PLAYER_EVENT
fi
