#!/bin/sh

SERVER="pecom@pecom.sakura.ne.jp"
HOME="/home/pecom"

rsync -a -v --exclude ".git" --delete . "$SERVER":"$HOME"/www/monstars/
