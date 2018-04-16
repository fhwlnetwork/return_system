#!/bin/sh
while [ 1 ] ; do  
	php /srun3/www/srun4-mgr/yii checkout/addlist > /dev/null&
	sleep 30
done