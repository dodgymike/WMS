class bird ($routerid,$anycastips,$areaid,$interface,$interfacerange,$inftype,$routes) {

package {'bird':
        ensure => present,
        }

service {'bird':
        ensure => running,
        enable => true,
        hasstatus => false,
        hasrestart => false
        }

file    {'/etc/bird.conf':
        ensure => file,
        content => template('bird/bird.conf.erb'),
        #notify => Service['bird']
        }
}

