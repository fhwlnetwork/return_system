#!/bin/sh
#online.sh
#每隔一分钟,统计一下在线用户数,将在线数写入到 mysql
#author LiWenYu <liwenyu66@126.com>
#crontab */1 * * * * /srun3/www/srun4-mgr/shell/online.sh

php /srun3/www/srun4-mgr/yii online/index