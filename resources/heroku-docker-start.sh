#!/usr/bin/env bash

a2dismod mpm_event
sed -i "s/Listen 80/Listen ${PORT:-80}/g" /etc/apache2/ports.conf
apache2-foreground "$@"
