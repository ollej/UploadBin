#!/bin/sh
# Get a top list of all md5sums in cwd.

DIR=$1
if [ -z $DIR ]; then
	DIR=`cwd`
fi

cd "$DIR"
ls -1t | xargs md5sum | sort | uniq  -d -c -w 32 | sort -g 
