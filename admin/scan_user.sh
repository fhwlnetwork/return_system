#!/bin/sh
while [ 1 ] ; do  
	php /srun3/www/srun4-mgr/yii cron/scan_user > /dev/null&
	sleep 600
done