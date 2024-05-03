#!/bin/bash

# 檢查 Apache 服務狀態
if /usr/sbin/service apache2 status > /dev/null; then
    echo "Apache is running."
else
    echo "Apache is not running. Restarting..."
    /usr/sbin/service apache2 start
fi