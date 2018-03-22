#!/usr/bin/php
<?php

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


		Author			: RATEFIARISON Aina Manoa
									manoaratefy@hostibox.com
									https://manoaratefy.hostibox.com
									Version 1.0

    Original author: contact@dotmana.com

*/

echo "Subject: Daily backup report \n";
echo "\n";

$ftp_host = "changeme";						// Remote FTP host
$ftp_user = "changeme";						// Remote FTP user
$ftp_password = "changeme";				// Remote FTP password

$mysql_user = "root";
$mysql_password = "changeme";			// MySQL root password

$backup_dir = "/home/backup/";		// directory to write backup before sending them to ftp
$websites_dir = "/var/www/";			// directory where your websites are (with ISPConfig, it should be there)

$excluded_databases = array(			// Databases which are excluded to backup
	'information_schema',
	'performance_schema',
	'mysql',
);

echo "\nStart backup at ".date('d/m/Y H:i:s')."\n";

// backup each websites and send bzip to ftp
$dh  = opendir($websites_dir);
while (false !== ($filename = readdir($dh))) {
    $files[] = $filename;
}
sort($files);
foreach($files as $website)
{
	if (is_link($websites_dir.$website))
	{
		echo "\nBackuping data from website ".$website;
		exec('tar cjf '.$backup_dir.$website.'.bz2  -C '.readlink($websites_dir.$website).' . ');

		if (file_exists($backup_dir.$website.'.bz2'))
  		{
			$ftp_con = ftp_connect($ftp_host);
			$login_result = ftp_login($ftp_con, $ftp_user, $ftp_password);
			$result_ftp = ftp_put($ftp_con, $website.'.bz2', $backup_dir.$website.'.bz2', FTP_BINARY);
			ftp_close($ftp_con);
			echo "\n--> FTP : status ".var_export($result_ftp,true);
  		}

	}
}

// backup each databases and send bzip to ftp
$link = new PDO('mysql:host=localhost;charset=utf8', $mysql_user, $mysql_password);
$db_list = $link->query("SHOW DATABASES");
while ($row = $db_list->fetch())
{
  if(in_array($row['Database'], $excluded_databases))
  {
	continue;
  }

  echo "\nBackuping data from database ".$row['Database'];
  exec(
	sprintf(
		'/usr/bin/mysqldump %s --host=%s --user=%s --password=%s > %s',
		$row['Database'],
		"localhost",
		$mysql_user,
		$mysql_password,
		$backup_dir.$row['Database'].'.sql'
	)
  );
  if (file_exists($backup_dir.$row['Database'].'.sql'))
  {
		exec('bzip2 -f '.$backup_dir.$row['Database'].'.sql');
  }

  if (file_exists($backup_dir.$row['Database'].'.sql.bz2'))
  {
	$ftp_con = ftp_connect($ftp_host);
	$login_result = ftp_login($ftp_con, $ftp_user, $ftp_password);
	$result_ftp = ftp_put($ftp_con, $row['Database'].'.sql.bz2', $backup_dir.$row['Database'].'.sql.bz2', FTP_BINARY);
	ftp_close($ftp_con);
	echo "\n--> FTP : status ".var_export($result_ftp, true);

  }

}

echo "\nFinish backup at ".date('d/m/Y H:i:s');


?>
