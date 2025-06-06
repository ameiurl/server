#!/bin/bash

. /etc/profile

# DIR=/var/lib/mysql/backup
DIR=/home/amei/mysql/backup
if [ ! -e $DIR ]
then
/bin/mkdir -p $DIR
fi
NOWDATE=$(date +%Y%m%d%H%M%S)
/usr/bin/mysqldump --all-databases -uroot -p "root" | gzip > "$DIR/data_$NOWDATE.sql.gz"

/usr/bin/find $DIR -mtime +7 -name "data_[1-9]*.sql.gz" -exec rm -rf {} \;
