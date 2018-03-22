# ispcfgbk - ISPConfig Backup Script
I've written this backup script to make backup for my web servers which run ISPConfig. Based on the script I've found here (http://www.dotmana.com/weblog/2011/09/backup-de-vos-sites-sur-ftp-avec-ispconfig/), I've made some adjustements to make it running on Debian 9 (Stretch):
- Change MySQL engine to PDO as mysql_connect is no more available on PHP 7 which is the default php-cli version on Debian 9.
- Created a way to exclude some databases during backup.

# How to setup it?
Firstly, download backup-ispconfig.php to /root/ folder.

After that, create the backup folder:

mkdir /home/backup

And finally, create a cron job to automate backup:

crontab -e

30 1 * * * /root/scripts/backup-ispconfig.php | /usr/sbin/sendmail <your-email@example.com>

As you might see, the crontab send a report email.

# Tested on
Debian 9, MariaDB 10.0.34, ISPConfig 3.1.11
