#!/bin/sh

set -e
UPLOADDIR=/data/ftp/upload
BACKUPDIR=/data/www/wms.ctwug.za.net/backup

cd ${UPLOADDIR}
pserial="0"
now15=$(date --date="15 minutes ago" +%s)
now3d=$(date --date="3 days ago" +%Y%m%d)
now1w=$(date --date="1 week ago" +%Y%m%d)
now2w=$(date --date="2 weeks ago" +%Y%m%d)
now3w=$(date --date="3 weeks ago" +%Y%m%d)
now4w=$(date --date="4 weeks ago" +%Y%m%d)
now5w=$(date --date="5 weeks ago" +%Y%m%d)

# Move latest backups
for b in $(ls)
do
	# strip off .backup extension from file name
	serial=${b%*.backup}
	if [ "${serial}" = "${b}" ]
	then
		# Not a backup
		continue
	fi
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
	dst=${serial}-${btime}.backup
	mv ${b} ${BACKUPDIR}/${serial}/${dst}
	cd ${BACKUPDIR}/${serial}
	chgrp www ${dst}
	chmod g+r ${dst}
	sha1sum ${dst} >> SHA1SUMS
	cd ${UPLOADDIR}
done

cd ${BACKUPDIR}

# Delete old backups
for serial in $(ls)
do
	week1=true
	week2=true
	week3=true
	week4=true
	for b in $(ls ${serial})
	do
		if [ "${b}" = "SHA1SUMS" ]
		then
			continue
		fi
		btime=${b#${serial}-*}
		btime=${btime%*.backup}
		if echo ${btime} |grep -v -q '^2[01][0-9][0-9][01][0-9][0-3][0-9]'
		then
			echo invalid date format: ${btime} >&2
			continue
		fi
		if [ ${btime} -ge ${now3d} ]
		then
			# Keep backups 3 days old or younger
			continue
		fi
		if ${week1} && [ ${btime} -le ${now1w} -a ${btime} -gt ${now2w} ]
		then
			# Keep first backup that's 1 week old
			week1=false
			continue
		fi
		if ${week2} && [ ${btime} -le ${now2w} -a ${btime} -gt ${now3w} ]
		then
			# Keep first backup that's 2 weeks old
			week2=false
			continue
		fi
		if ${week3} && [ ${btime} -le ${now3w} -a ${btime} -gt ${now4w} ]
		then
			# Keep first backup that's 3 weeks old
			week3=false
			continue
		fi
		if ${week4} && [ ${btime} -le ${now4w} -a ${btime} -gt ${now5w} ]
		then
			# Keep first backup that's 4 weeks old
			week4=false
			continue
		fi
		rm ${serial}/${b}
		sed -r -i"" "/^[a-f0-9]{40} *${b}\$/ d" ${serial}/SHA1SUMS
	done
done
