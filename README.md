WMS
===

WUG Management System

## A Project to manage Mikrotik and UBNT routers

Basic goals for the system are:

- radius
  - rb access
  - wireless access lists
  - DHCP settings

- rb scripts
  - script to remove *all* of the ctwug scripts, LDAP settings etc
  - main script to check for new scripts aka ctwug_init
  - gametime check script
  - firewall script
  - web server to serve the above
    - highly optimised PHP page to serve scripts + flags (gametime etc)

- front-end
  - set of pages for all radius db entries
  - set of pages for scripts
  - set of pages for gametime setup
  - set of pages for firewall setup

- stats for all of the above
  - Zabbix/Cacti/Nagios/Smokeping

