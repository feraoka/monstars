#!/bin/sh

TOP=..
RAW=data/raw
XML=$TOP/data/xml
WORKDIR=./work

RAWFILES=`ls $TOP/$RAW |grep -v -E .*~`

if [ ! -d $XML ]; then
    mkdir $XML
fi

if [ ! -d $WORKDIR ]; then
	mkdir $WORKDIR
fi

for FILE in $RAWFILES
do
	XMLFILE=${FILE%txt}
	XMLFILE=${XMLFILE%htm}xml
	echo "Processing" $RAW/$FILE
	nkf -s $TOP/$RAW/$FILE > $WORKDIR/$FILE
	./xmlgen.pl $WORKDIR/$FILE > $XML/$XMLFILE
done
