#!/bin/sh

\rm -f data/monstars.db
git log HEAD^..HEAD | grep Date > lastupdate.txt

cd tools
./refreshxml.sh
cd ..

cd php
php create_db.php


echo "creating game history image"
php resultHistory.php > ../images/resultHistory.png
echo "creating member history image"
php memberHistory.php > ../images/memberHistory.png
