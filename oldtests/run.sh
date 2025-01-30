#!/bin/sh
if [ $? != 0 ]; then
	exit 1
fi
pear run-tests -r
