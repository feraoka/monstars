#!/bin/sh
svn info > svnInfo.txt
cd tools
./refreshxml.sh
cd ..
\rm /dev/shm/monstars.db
cd php
php create_db.php
cp /dev/shm/monstars.db ../data/
echo "creating game history image"
php resultHistory.php > ../images/resultHistory.png
echo "creating member history image"
php memberHistory.php > ../images/memberHistory.png
