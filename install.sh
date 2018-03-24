#!/bin/sh

echo "Generating configuration..."
php think build
echo "Please manually config ./application/database.php later.^_^"

echo "Generating database structure..."
read -p "Enter mysql account:" account
mysql -u $account -p -e "source ./install/ass.sql"

echo "Finished, have fun."
