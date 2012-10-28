#!/bin/sh

TOP=..
RAW=data/raw
XML=/dev/shm/xml

RAWFILES=`ls $TOP/$RAW |grep -v -E .*~`

mkdir $XML
for FILE in $RAWFILES
do
	XMLFILE=${FILE%txt}
	XMLFILE=${XMLFILE%htm}xml
	echo "Processing" $RAW/$FILE
	./xmlgen.pl $TOP/$RAW/$FILE > $XML/$XMLFILE
done


