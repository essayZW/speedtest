#!/bin/bash

set -e
set -x

# Cleanup
rm -rf /var/www/html/*

# Copy web files
cp -R /speedtest/* /var/www/html/

sed -i s/\$IPINFO_APIKEY=\"\"/\$IPINFO_APIKEY=\"$IPINFO_APIKEY\"/g /var/www/html/backend/getIP_ipInfo_apikey.php

# Apply Telemetry settings when running in standalone or frontend mode and telemetry is enabled
if [[ "$TELEMETRY" == "true" && ( "$MODE" == "frontend" || "$MODE" == "standalone" ) ]]; then

  sed -i s/\$stats_password=\".*\"/\$stats_password=\"$PASSWORD\"/g /var/www/html/results/telemetry_settings.php
  # set mysql database info
  sed -i s/\$MySql_username=\".*\"/\$MySql_username=\"$MYSQL_USER\"/g /var/www/html/results/telemetry_settings.php
  sed -i s/\$MySql_password=\".*\"/\$MySql_password=\"$MYSQL_PASSWORD\"/g /var/www/html/results/telemetry_settings.php
  sed -i s/\$MySql_hostname=\".*\"/\$MySql_hostname=\"$MYSQL_HOST\"/g /var/www/html/results/telemetry_settings.php
  sed -i s/\$MySql_databasename=\".*\"/\$MySql_databasename=\"$MYSQL_DATABASE\"/g /var/www/html/results/telemetry_settings.php
  sed -i s/\$MySql_port=\".*\"/\$MySql_port=\"$MYSQL_PORT\"/g /var/www/html/results/telemetry_settings.php

  if [ "$ENABLE_ID_OBFUSCATION" == "true" ]; then
    sed -i s/\$enable_id_obfuscation=.*\;/\$enable_id_obfuscation=true\;/g /var/www/html/results/telemetry_settings.php
  fi

  if [ "$REDACT_IP_ADDRESSES" == "true" ]; then
    sed -i s/\$redact_ip_addresses=.*\;/\$redact_ip_addresses=true\;/g /var/www/html/results/telemetry_settings.php
  fi

fi

chown -R www-data /var/www/html/*

# Allow selection of Apache port for network_mode: host
if [ "$WEBPORT" != "80" ]; then
  sed -i "s/^Listen 80\$/Listen $WEBPORT/g" /etc/apache2/ports.conf
  sed -i "s/*:80>/*:$WEBPORT>/g" /etc/apache2/sites-available/000-default.conf
fi

echo "Done, Starting APACHE"

# This runs apache
apache2-foreground
