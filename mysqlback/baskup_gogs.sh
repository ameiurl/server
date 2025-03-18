#!/bin/bash
# DIR=/var/lib/mysql/backup
DIR=/server/mysql/backup
if [ ! -e $DIR ]
then
/bin/mkdir -p $DIR
fi
NOWDATE=$(date +%Y%m%d%H%M%S)
docker exec mydb mysqldump -uroot -proot gogs | gzip > "$DIR/data_$NOWDATE.sql.gz" 
send=`date '+%Y-%m-%d %H:%M:%S'`
if [ $? -ne 0 ];
then
    echo "$send 数据备份失败"
    exit -1
else
    echo "$send 数据备份成功！"
fi
 
find $DIR -mtime +7 -name 'data_[1-9].sql.gz' -exec rm -rf {} \;
