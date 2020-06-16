@echo off
rem FIND LOCAL IP ADDRESS OF THE SERVER
rem @package       CF Geo Plugin
rem @version       1.0.0
rem @since         7.11.2
rem @author        Ivijan-Stefan Stipic
setlocal
setlocal enabledelayedexpansion
rem throw away everything except the IPv4 address line 
for /f "usebackq tokens=*" %%a in (`ipconfig ^| findstr IPv4`) do (
	rem we have for example "IPv4 Address. . . . . . . . . . . : 192.168.0.1"
	rem split on ':' and get 2nd token
	for /f delims^=^:^ tokens^=2 %%b in ('echo %%a') do (
		rem we have " 192.168.0.1"
		rem split on '.' and get 4 tokens (octets)
		for /f "tokens=1-4 delims=." %%c in ("%%b") do (
			set _o1=%%c
			rem strip leading space from first octet
			set _output=!_o1:~1!.%%d.%%e.%%f
			echo !_output!
		)
	)
)
endlocal