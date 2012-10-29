#!/bin/sh

TOP=..
RAW=data/raw
XML=$TOP/data/xml

RAWFILES=`ls $TOP/$RAW |grep -v -E .*~`

if [ ! -d $XML ]; then
    mkdir $XML
fi

for FILE in $RAWFILES
do
	XMLFILE=${FILE%txt}
	XMLFILE=${XMLFILE%htm}xml
	echo "Processing" $RAW/$FILE
	./xmlgen.pl $TOP/$RAW/$FILE > $XML/$XMLFILE
done


