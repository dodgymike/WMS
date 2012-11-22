insert into script_type (name) values ('routeros');
insert into script_group (name) values ('common');


insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('radius_client', 1, 1, 'bootstrap', '0000000001', true, ' 
g info ("adding radius entry"); 
  /radius add service=login address=172.18.0.1 secret="CTWug!!" 
}
/user aaa set use-radius=yes');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_init', 1, 1, 'bootstrap', '0000000001', true, '
/system script
:if ([find name=ctwug_init] != "" ) do=[remove ctwug_init]
add name=ctwug_init policy=read,write,test source=":local fid [/system identity get name]\n:local fserial [/system routerboard get serial-number]\n:local ffile \"ctwug_version.rsc\"\n:local fdns 0\n:local oldDns \"\"\n\n:foreach server in [/ip dns get servers] do={\n :if (\$server = \"172.18.1.1\") do={ :set fdns 1; }\n :set oldDns (\$oldDns.\$server.\",\")\n}\n:if (\$fdns = 0) do={\n :set oldDns (\$oldDns.\"172.18.1.1\")\n /ip dns set servers=\$oldDns\n}\n\n:local fpath (\"web/api/update?id=\".\$fid.\"&serial=\".\$fserial.\"&init=1\")\n:local fpath2 \$fpath\n:local fpath \"\"\n:for i from=0 to=( [:len \$fpath2] - 1) do={\n  :local fchar [:pick \$fpath2 \$i]\n  :if ( \$fchar = \" \") do={\n    :set fchar \"%20\"\n  }\n  :set fpath (\$fpath.\$fchar)\n}\n\n/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=\$fpath dst-path=\$ffile mode=http\n:delay 1\n:local temp [/file get \$ffile size]\n:if ( \$temp > 2) do={\n  /import \$ffile\n}\n"

/system script set ctwug_init policy=reboot,read,write,policy,test,password,sniff,sensitive

/system script
:if ([find name=ctwug_version] != "" ) do=[remove ctwug_version]
add name=ctwug_version policy=read,write,test source=":local fid [/system identity get name]\n:local fserial [/system routerboard get serial-number]\n:local fver [/system resource get version]\n:local fcpu [/system resource get cpu]\n:local fcpufreq [/system resource get cpu-frequency]\n:local farch [/system resource get architecture-name]\n:local fboard [/system resource get board-name]\n:local ffw [/system routerboard get current-firmware]\n:local fip [/ip address get 0 address]\n:local ffile \"ctwug_version.rsc\"\n:local fpolicy 0\n\n/user\n:local fospf 0\n:foreach id in [/user find name=ctwug_ospf] do={\n  :set fospf 1\n}\n\n:if ([/system script find name=ctwug_init] != \"\" ) do={\n  :local fpolicys [/system script get ctwug_init policy]\n  :for i from=0 to=([:len \$fpolicys] -1) do={\n    :local fchar [:pick \$fpolicys \$i]\n    :if (\$fchar = \"reboot\") do={:set fpolicy (\$fpolicy+1)}\n    :if (\$fchar = \"read\") do={:set fpolicy (\$fpolicy+2)}\n    :if (\$fchar = \"write\") do={:set fpolicy (\$fpolicy+4)}\n    :if (\$fchar = \"policy\") do={:set fpolicy (\$fpolicy+8)}\n    :if (\$fchar = \"test\") do={:set fpolicy (\$fpolicy+16)}\n    :if (\$fchar = \"password\") do={:set fpolicy (\$fpolicy+32)}\n    :if (\$fchar = \"sniff\") do={:set fpolicy (\$fpolicy+64)}\n    :if (\$fchar = \"sensitive\") do={:set fpolicy (\$fpolicy+128)}\n  }\n}\n\n:local fpath (\"web/api/update?id=\".\$fid.\"&serial=\".\$fserial.\"&update=2&version=\".\$fver.\"&cpu=\".\$fcpu.\"&freq=\".\$fcpufreq.\"&arch=\".\$farch.\"&board=\".\$fboard.\"&fw=\".\$ffw.\"&ip=\".\$fip.\"&ospf=\".\$fospf.\"&policy=\".\$fpolicy)\n:local fpath2 \$fpath\n:local fpath \"\"\n:for i from=0 to=( [:len \$fpath2] - 1) do={\n  :local fchar [:pick \$fpath2 \$i]\n  :if (\$fchar = \" \") do={\n    :set fchar \"%20\"\n  }\n  :set fpath (\$fpath.\$fchar)\n}\n\n/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=\$fpath dst-path=\$ffile mode=http\n:delay 1\n:local temp [/file get \$ffile size]\n:if ( \$temp > 2) do={\n  /import \$ffile\n}"

/system script set ctwug_version policy=reboot,read,write,policy,test,password,sniff,sensitive

/system script
:if ([find name=ctwug_backup] != "" ) do=[remove ctwug_backup]
add name=ctwug_backup policy=read,write,test source=":local fid [/system identity get name]\n:local fserial [/system routerboard get serial-number]\n\n:if ( [/file find name=ctwug-auto.backup] != \"\" ) do=[/file remove ctwug-auto.backup]\n:delay 1\n/system backup save name=ctwug-auto\n:local fwait 1\n:local fcnt 0\n:while ( \$fwait = 1 ) do={\n  :set fcnt (\$fcnt+1)\n  :log info (\"ctwug_backup sleep \".\$fcnt)\n  :delay 1\n  if ([/file find name=ctwug-auto.backup] != \"\") do={ :set fwait 0; }\n  if ( \$fcnt = 20) do={\n    :log info \"ctwug_backup FAILED\";\n    :set fwait 0;\n  };\n};\n:local femail backup@ctwug.za.net\n:local fserver 172.18.55.25\n/tool e-mail send server=\$fserver from=\$femail to=\$femail subject=\"\$fid/\$fserial\" file=ctwug-auto.backup"

/system script set ctwug_backup policy=reboot,read,write,policy,test,password,sniff,sensitive

/system script
:if ([find name=ctwug_updated] != "" ) do=[remove ctwug_updated]
add name=ctwug_updated policy=read,write,test source=":local fid [/system identity get name]\n:local fserial [/system routerboard get serial-number]\n:local ffile \"ctwug_version.rsc\"\n\n:local fpath (\"web/api/update?id=\".\$fid.\"&serial=\".\$fserial.\"&update=1\")\n:local fpath2 \$fpath\n:local fpath \"\"\n:for i from=0 to=( [:len \$fpath2] - 1) do={\n  :local fchar [:pick \$fpath2 \$i]\n  :if ( \$fchar = \" \") do={\n    :set fchar \"%20\"\n  }\n  :set fpath (\$fpath.\$fchar)\n}\n\n/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=\$fpath dst-path=\$ffile mode=http\n:delay 1\n:log info [/file get \$ffile contents]"

/system script
:if ([find name=ctwug_global_settings] != "" ) do=[remove ctwug_global_settings]
add name=ctwug_global_settings policy=read,write,test source="#/ip dns set allow-remote-requests=yes primary-dns=172.18.1.1\n/system clock set time-zone-name=Africa/Johannesburg\n/system ntp client set enabled=yes mode=unicast primary-ntp=172.18.1.1\n/snmp set enabled=yes\n/ip firewall connection tracking set enabled=yes\n\n/radius\n:local id\n:local fto 00:00:02\n:local fadd 172.18.0.1\n:local ffound 0\n:foreach id in [find comment=CTWUG] do={\n  :if ( [get \$id timeout] != \$fto ) do=[set \$id timeout=\$fto]\n  :if ( [get \$id address] != \$fadd ) do=[set \$id address=\$fadd]\n  set \$id comment=CTWUG\n  :set ffound 1\n}\n:if (\$ffound = 0) do={\n :foreach id in [find] do={\n   :if ( [get \$id timeout] != \$fto ) do=[set \$id timeout=\$fto]\n   :if ( [get \$id address] != \$fadd ) do=[set \$id address=\$fadd]\n   set \$id comment=CTWUG\n }\n}\n/system script\n:local fs\n:foreach fs in [find] do={\n :if ( [:pick [get \$fs name] 0 10] = \"ctwug_mpls\" ) do=[remove \$fs]\n :if ( [:pick [get \$fs name] 0 13] = \"ctwug_netflow\" ) do=[remove \$fs]\n}\n:if [/ip traffic-flow get enabled] do={\n  /log info (\"disabling netflows\")\n  /ip traffic-flow set enabled=no\n}"

/system script
:if ([find name=ctwug_cpu_killer_killer] != "" ) do=[remove ctwug_cpu_killer_killer]
add name=ctwug_cpu_killer_killer policy=read,write,test source="log info \"running cpu killer killer\"\n\n:foreach i in=[/system script job find] do={\n :local scriptname [/system script job get \$i script];\n\n log info (\"scriptname (\".\$scriptname.\")\");\n :if ([:len \$scriptname]=0) do={\n   log info (\"skipping empty script\");\n } else {\n   log info (\"calling script killer\");\n   /system script job remove \$i\n }\n\n}\n\nlog debug \"cpu killer killer done\";"

/system script
:if ([find name=ctwug_radius_client] != "" ) do=[remove ctwug_radius_client]
add name=ctwug_radius_client policy=read,write,test source=":if ([:len [/radius find address=172.18.0.1 ]]=0) do={ \n  log info (\"adding radius entry\"); \n  /radius add service=login address=172.18.0.1 secret=\"CTWug!!\" \n}\n/user aaa set use-radius=yes"

/system script
:if ([find name=ctwug_firewall] != "" ) do=[remove ctwug_firewall]
add name=ctwug_firewall policy=read,write,test source=":local ffile \"firewall.rsc\"\n/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=\"web/api/firewall\" dst-path=\$ffile mode=http\n:delay 1\n/import \$ffile\n/system script run ctwug_run"

/system script
:if ([find name=ctwug_qos] != "" ) do=[remove ctwug_qos]
add name=ctwug_qos policy=read,write,test source="/queue simple\n:local qos\n:foreach qos in [find] do={\n :if ( [:pick [get \$qos comment] 0 4] = \"AUTO\" ) do=[remove \$qos]\n}\n/interface\n:local qos\n:foreach qos in [find] do={\n :local com [get \$qos comment]\n :if ( [:pick \$com 0 6] = \"client\" ) do={\n  :local name [get \$qos name]\n  :local p1 ([:find \$com \";\"] + 1)\n  :local p2 [:find \$com \";\" \$p1]\n  :local mb ([:pick \$com \$p1 \$p2]*1000)\n  :local com (\"AUTO \".\$name.\" BULK\")\n  /queue simple add interface=\$name name=\$com packet-marks=BULK comment=\$com max-limit=(\"0/\".\$mb) direction=download disabled=yes\n }\n}\n/system script run ctwug_run"

/system script
:if ([find name=ctwug_run] != "" ) do=[remove ctwug_run]
add name=ctwug_run policy=read,write,test source="#:if ( [/file find name=is_gametime.txt] != \"\" ) do=[/file remove is_gametime.txt]\n/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=web/api/gametime dst-path=is_gametime.txt mode=http\n:delay 1\n:local temp [/file get is_gametime.txt contents]\n:local fdisabled no\n:if (\$temp = 0) do={:set fdisabled yes}\n\n/queue simple\n:local qos\n:foreach qos in [find disabled!=\$fdisabled] do={\n :if ( [:pick [get \$qos comment] 0 4] = \"AUTO\" ) do=[set \$qos disabled=\$fdisabled]\n}\n\n/interface\n:local iface\n:foreach iface in [find disabled=no] do={\n :if ( [:pick [get \$iface comment] 0 4] = \"qos;\" ) do=[ :set fdisabled no]\n}\n\n/ip firewall mangle\n:local fw\n:foreach fw in [find disabled!=\$fdisabled] do={\n :if ( [:pick [get \$fw comment] 0 4] = \"AUTO\" ) do=[set \$fw disabled=\$fdisabled]\n}"

/system script
:if ([find name=ctwug_lobridge_fixer] != "" ) do=[remove ctwug_lobridge_fixer]
add name=ctwug_lobridge_fixer policy=read,write,test source=":foreach iobridge in=[/interface bridge find name=iobridge] do={\n  /interface bridge set \$iobridge name=lobridge;\n}\n\n:if ([:len [/interface bridge find name=lobridge]]=0) do={\n  log info \"creating lobridge\";\n\n  /interface bridge add name=lobridge; \n}\n\n:if ([:len [/ip address find interface=lobridge]]=0) do={\n\n  :local backbonearea [/routing ospf area get [/routing ospf area find area-id=0.0.0.0] name]\n\n  log info (\"checking backbonearea (\".\$backbonearea.\")\");\n\n  :local ospfip \"\"\n  :foreach backbone in=[/routing ospf network find area=\$backbonearea] do={\n    :local bbnet [/routing ospf network get \$backbone network]\n    log info (\"checking bbnet (\".\$bbnet.\")\");\n\n    :for i from=0 to=([:len \$bbnet] - 1) do={ \n      :if ( [:pick \$bbnet \$i] = \"/\") do={ \n        :local tmp [:pick \$bbnet 0 \$i]\n        :set bbnet \$tmp;\n      } \n    }\n\n    log info (\"checking bbnet (\".\$bbnet.\")\");\n    :set ospfip [/ip address get  [/ip address find network=\$bbnet] address]\n  }\n\n  :for i from=0 to=([:len \$ospfip] - 1) do={ \n      :if ( [:pick \$ospfip \$i] = \"/\") do={ \n        :local tmp [:pick \$ospfip 0 \$i]\n        :set ospfip \$tmp;\n      } \n    }\n\n  log info (\"current ospf ip (\".\$ospfip.\")\");\n  \n  /ip address add address=(\$ospfip.\"/32\") interface=lobridge\n}"

:local found 0
:foreach id in [/user find name=ctwug_ospf] do={
  :set found 1
  /user set $id password=REDACTED group=full disabled=no
}
:if ($found = 0) do={
  /user add name=ctwug_ospf password=REDACTED group=full
}
:local found 0
:foreach id in [/user find name=ctwug] do={
  :set found 1
  /user set $id password=ctwug group=read disabled=no
}
:if ($found = 0) do={
  /user add name=ctwug password=ctwug group=read
}
/system schedule
:local qos
:foreach qos in [find] do={
 :if ( [:pick [get $qos comment] 0 4] = "AUTO" ) do=[remove $qos]
}
add comment="AUTO ctwug_version" interval=3600 name=ctwug_version on-event=ctwug_version start-time=00:00:00
add comment="AUTO ctwug_backup" interval=86400 name=ctwug_backup on-event=ctwug_backup start-time=23:00:00
add comment="AUTO ctwug_run" interval=1200 name=ctwug_run on-event=ctwug_run start-time=00:00:00
/system script run ctwug_updated
/system script run ctwug_global_settings
/system script run ctwug_radius_client
/system script run ctwug_firewall
/system script run ctwug_qos
/system script run ctwug_version
:local fid [/system identity get name]
:local fserial [/system routerboard get serial-number]
:local fpath ("web/api/temp?id=".$fid."&serial=".$fserial)
:local fpath2 $fpath
:local fpath ""
:for i from=0 to=( [:len $fpath2] - 1) do={
  :local fchar [:pick $fpath2 $i]
  :if ( $fchar = " ") do={
    :set fchar "%20"
  }
  :set fpath ($fpath.$fchar)
}
/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=$fpath dst-path=ctwug_version.rsc mode=http
:delay 1
:log info "ctwug_init done"
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_version', 1, 1, 'bootstrap', '0000000001', true, '
:local fid [/system identity get name]
:local fserial [/system routerboard get serial-number]
:local fver [/system resource get version]
:local fcpu [/system resource get cpu]
:local fcpufreq [/system resource get cpu-frequency]
:local farch [/system resource get architecture-name]
:local fboard [/system resource get board-name]
:local ffw [/system routerboard get current-firmware]
:local fip [/ip address get 0 address]
:local ffile "ctwug_version.rsc"
:local fpolicy 0

/user
:local fospf 0
:foreach id in [/user find name=ctwug_ospf] do={
  :set fospf 1
}

:if ([/system script find name=ctwug_init] != "" ) do={
  :local fpolicys [/system script get ctwug_init policy]
  :for i from=0 to=([:len $fpolicys] -1) do={
    :local fchar [:pick $fpolicys $i]
    :if ($fchar = "reboot") do={:set fpolicy ($fpolicy+1)}
    :if ($fchar = "read") do={:set fpolicy ($fpolicy+2)}
    :if ($fchar = "write") do={:set fpolicy ($fpolicy+4)}
    :if ($fchar = "policy") do={:set fpolicy ($fpolicy+8)}
    :if ($fchar = "test") do={:set fpolicy ($fpolicy+16)}
    :if ($fchar = "password") do={:set fpolicy ($fpolicy+32)}
    :if ($fchar = "sniff") do={:set fpolicy ($fpolicy+64)}
    :if ($fchar = "sensitive") do={:set fpolicy ($fpolicy+128)}
  }
}

:local fpath ("web/api/update? id=".$fid."&serial=".$fserial."&update=2&version=".$fver."&cpu=".$fcpu."&freq=".$fcpufreq."&arch=".$farch."&board=".$fboard."&fw=".$ffw."&ip=".$fip."&ospf=".$fospf."& policy=".$fpolicy) 
:local fpath2 $fpath
:local fpath ""
:for i from=0 to=( [:len $fpath2] - 1) do={
  :local fchar [:pick $fpath2 $i]
  :if ($fchar = " ") do={
    :set fchar "%20"
  }
  :set fpath ($fpath.$fchar)
}

/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=$fpath dst-path=$ffile mode=http
:delay 1
:local temp [/file get $ffile size]
:if ( $temp > 2) do={
  /import $ffile
}
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_backup', 1, 1, 'bootstrap', '0000000001', true, '
:local fid [/system identity get name]
:local fserial [/system routerboard get serial-number] 

:if ( [/file find name=ctwug-auto.backup] != "" ) do=[/file remove ctwug-auto.backup]
:delay 1
/system backup save name=ctwug-auto
:local fwait 1
:local fcnt 0
:while ( $fwait = 1 ) do={
  :set fcnt ($fcnt+1)
  :log info ("ctwug_backup sleep ".$fcnt)
  :delay 1
  if ([/file find name=ctwug-auto.backup] != "") do={ :set fwait 0; }
  if ( $fcnt = 20) do={
    :log info "ctwug_backup FAILED";
    :set fwait 0;
  };
};
:local femail backup@ctwug.za.net
:local fserver 172.18.55.25
/tool e-mail send server=$fserver from=$femail to=$femail subject="$fid/$fserial" file=ctwug-auto.backup
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_updated', 1, 1, 'bootstrap', '0000000001', true, '
:local fid [/system identity get name]
:local fserial [/system routerboard get serial-number]
:local ffile "ctwug_version.rsc"       
:local fpath ("web/api/update?id=".$fid."&serial=".$fserial."&update=1")
:local fpath2 $fpath
:local fpath ""
:for i from=0 to=( [:len $fpath2] - 1) do={
  :local fchar [:pick $fpath2 $i]
  :if ( $fchar = " ") do={
    :set fchar "%20"
  }
  :set fpath ($fpath.$fchar)
}
       
/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=$fpath dst-path=$ffile mode=http
:delay 1
:log info [/file get $ffile contents]
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_global_settings', 1, 1, 'bootstrap', '0000000001', true, '
#/ip dns set allow-remote-requests=yes primary-dns=172.18.1.1
/system clock set time-zone-name=Africa/Johannesburg
/system ntp client set enabled=yes mode=unicast primary-ntp=172.18.1.1
/snmp set enabled=yes
/ip firewall connection tracking set enabled=yes
/radius
:local id
:local fto 00:00:02
:local fadd 172.18.0.1
:local ffound 0
:foreach id in [find comment=CTWUG] do={
  :if ( [get $id timeout] != $fto ) do=[set $id timeout=$fto]
  :if ( [get $id address] != $fadd ) do=[set $id address=$fadd]
  set $id comment=CTWUG
  :set ffound 1
}
:if ($ffound = 0) do={
  :foreach id in [find] do={
    :if ( [get $id timeout] != $fto ) do=[set $id timeout=$fto]
    :if ( [get $id address] != $fadd ) do=[set $id address=$fadd]
    set $id comment=CTWUG
  }
}
/system script
:local fs
:foreach fs in [find] do={
  :if ( [:pick [get $fs name] 0 10] = "ctwug_mpls" ) do=[remove $fs]
  :if ( [:pick [get $fs name] 0 13] = "ctwug_netflow" ) do=[remove $fs]
}
:if [/ip traffic-flow get enabled] do={
  /log info ("disabling netflows")
  /ip traffic-flow set enabled=no
}
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_cpu_killer_killer', 1, 1, 'bootstrap', '0000000001', true, '
log info "running cpu killer killer"

:foreach i in=[/system script job find] do={
 :local scriptname [/system script job get $i script];

 log info ("scriptname (".$scriptname.")");
 :if ([:len $scriptname]=0) do={
   log info ("skipping empty script");
 } else {
   log info ("calling script killer");
   /system script job remove $i
 }

}

log debug "cpu killer killer done";
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_radius_client', 1, 1, 'bootstrap', '0000000001', true, '
:if ([:len [/radius find address=172.18.0.1 ]]=0) do={ 
  log info ("adding radius entry"); 
  /radius add service=login address=172.18.0.1 secret="CTWug!!" 
}
/user aaa set use-radius=yes
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_firewall', 1, 1, 'bootstrap', '0000000001', true, '
:local ffile "firewall.rsc"
/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path="web/api/firewall" dst-path=$ffile mode=http
:delay 1
/import $ffile
/system script run ctwug_run
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_qos', 1, 1, 'bootstrap', '0000000001', true, '
/queue simple
:local qos
:foreach qos in [find] do={
  :if ( [:pick [get $qos comment] 0 4] = "AUTO" ) do=[remove $qos]
}
/interface
:local qos
:foreach qos in [find] do={
  :local com [get $qos comment]
  :if ( [:pick $com 0 6] = "client" ) do={
    :local name [get $qos name]
    :local p1 ([:find $com ";"] + 1)
    :local p2 [:find $com ";" $p1]
    :local mb ([:pick $com $p1 $p2]*1000)
    :local com ("AUTO ".$name." BULK")
    /queue simple add interface=$name name=$com packet-marks=BULK comment=$com max-limit=("0/".$mb) direction=download disabled=yes
  }
}
/system script run ctwug_run
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_run', 1, 1, 'bootstrap', '0000000001', true, '
#:if ( [/file find name=is_gametime.txt] != "" ) do=[/file remove is_gametime.txt]
/tool fetch host=noc.ctwug.za.net address=noc.ctwug.za.net src-path=web/api/gametime dst-path=is_gametime.txt mode=http
:delay 1
:local temp [/file get is_gametime.txt contents]
:local fdisabled no
:if ($temp = 0) do={:set fdisabled yes} 

/queue simple
:local qos
:foreach qos in [find disabled!=$fdisabled] do={
 :if ( [:pick [get $qos comment] 0 4] = "AUTO" ) do=[set $qos disabled=$fdisabled]
}

/interface
:local iface
:foreach iface in [find disabled=no] do={
 :if ( [:pick [get $iface comment] 0 4] = "qos;" ) do=[ :set fdisabled no]
}

/ip firewall mangle
:local fw
:foreach fw in [find disabled!=$fdisabled] do={
 :if ( [:pick [get $fw comment] 0 4] = "AUTO" ) do=[set $fw disabled=$fdisabled]
}
');

insert into script (name, script_group_id, script_type_id, created_by, version, enabled, script_body) values ('ctwug_lobridge_fixer', 1, 1, 'bootstrap', '0000000001', true, '
:foreach iobridge in=[/interface bridge find name=iobridge] do={
  /interface bridge set $iobridge name=lobridge;
} 

:if ([:len [/interface bridge find name=lobridge]]=0) do={
  log info "creating lobridge"; 

  /interface bridge add name=lobridge; 
}

:if ([:len [/ip address find interface=lobridge]]=0) do={ 

  :local backbonearea [/routing ospf area get [/routing ospf area find area-id=0.0.0.0] name] 

  log info ("checking backbonearea (".$backbonearea.")");

  :local ospfip ""
  :foreach backbone in=[/routing ospf network find area=$backbonearea] do={
    :local bbnet [/routing ospf network get $backbone network]
    log info ("checking bbnet (".$bbnet.")");

    :for i from=0 to=([:len $bbnet] - 1) do={ 
      :if ( [:pick $bbnet $i] = "/") do={ 
        :local tmp [:pick $bbnet 0 $i]
        :set bbnet $tmp;
      } 
    }

    log info ("checking bbnet (".$bbnet.")");
    :set ospfip [/ip address get  [/ip address find network=$bbnet] address]
  }

  :for i from=0 to=([:len $ospfip] - 1) do={ 
      :if ( [:pick $ospfip $i] = "/") do={ 
        :local tmp [:pick $ospfip 0 $i]
        :set ospfip $tmp;
      } 
    }

  log info ("current ospf ip (".$ospfip.")");
  
  /ip address add address=($ospfip."/32") interface=lobridge
}
');

