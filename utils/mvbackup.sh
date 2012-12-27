#!/bin/sh

set -e
UPLOADDIR=/data/ftp/upload
BACKUPDIR=/data/www/wms.ctwug.za.net/backup

cd ${UPLOADDIR}
pserial="0"
now15=$(date --date="15 minutes ago" +%s)

for b in $(ls)
do
	# strip off .backup extension from file name
	serial=${b%*.backup}
	mtime=$(stat -c %Y ${b})
	if [ ${mtime} -gt ${now15} ]
	then
		# don't touch files modified in the last 15 minutes
		continue
	fi
	btime=$(date -d @${mtime} +%Y%m%d)
	if [ "${serial}" != "${pserial}" ]
	then
		if [ ! -d ${BACKUPDIR}/${serial} ]
		then
			mkdir ${BACKUPDIR}/${serial}
		fi
	fi
	pserial=${serial}
	dst=${BACKUPDIR}/${serial}/${serial}-${btime}.backup
	mv ${b} ${dst}
	chgrp www ${dst}
	chmod g+r ${dst}
	updates="${updates:+${updates} }${serial}"
done

for serial in ${updates}
do
	cd ${BACKUPDIR}/${serial}
	sha1sum * >SHA1SUMS
done
