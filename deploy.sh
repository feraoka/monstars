#!/bin/sh

SERVER="pecom@pecom.sakura.ne.jp"
HOME="/home/pecom"

rsync -a -v . "$SERVER":"$HOME"/www/monstars/
