$extlookup_datadir = "/etc/puppet/extdata"
$extlookup_precedence = ["%{fqdn}", "common"]
$concatdir = "/etc/puppet/tmpfiles"
import "/etc/puppet/nodes/*.pp"

