#!/bin/sh

# shellcheck disable=SC2039
echo -e "\033[33;1m"
echo -e "Fixing code"
echo -e "===========\033[0m"
echo

if [ -z "$1" ]
then
	directory='src'
	echo "No directory specified (default: src)."
	echo
else
	directory=$1
	echo "Directory:" $directory
	echo
fi

vendor/bin/php-cs-fixer -v fix $directory --rules='{"@Symfony": true}'