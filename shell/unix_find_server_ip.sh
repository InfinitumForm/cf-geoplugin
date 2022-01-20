#!/bin/bash
#######################################
##  FIND LOCAL IP ADDRESS OF THE SERVER
##  @package       CF Geo Plugin
##  @version       2.0.0
##  @since         7.11.2
##  @author        Ivijan-Stefan Stipic
#######################################
IP=$(/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}' || exit_on_error "")
if [ -z "$IP" ]
then
	IP=$(hostname -I | awk '{print $1}' || exit_on_error "")
fi
echo $IP