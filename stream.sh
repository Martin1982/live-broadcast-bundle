#!/bin/bash
TWITCH_STREAM_CODE = "your_code_here"
ffmpeg -re -i ./testvideo.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/$TWITCH_STREAM_CODE
