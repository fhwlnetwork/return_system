#!/bin/sh
while [ 1 ] ; do  
	php /srun3/www/srun4-mgr/yii cron/send-msg > /dev/null&
	sleep 10
done