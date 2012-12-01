node 'bastion.ctwug.za.net' {

class {'bird':
routes => ['172.18.1.7/32','172.18.1.10/32'],
routerid => '172.18.1.7',
anycastips => "[ 172.18.1.7/32, 172.18.1.10/32 ]",
areaid => '0.0.0.0',
interface => "ppp*",
interfacerange => '172.16.0.0/12',
inftype => 'ptp'
}
}
