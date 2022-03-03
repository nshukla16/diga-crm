#!/bin/bash
cd "$(dirname "$0")"
source .env
php artisan users:broadcast_front_changes > storage/logs/js_changed_event.log