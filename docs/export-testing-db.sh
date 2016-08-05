#!/bin/bash
# Updates the data dump used for the tests

# cd into the git root
cd $(git rev-parse --show-toplevel)

configfile="protected/config/main-test.php"

# Extract the db connection information from the config file using regex
db=$(      sed -ne "s/ *'connectionString'      => 'mysql:host=127.0.0.1;dbname=\(.*\)',.*/\1/p" ${configfile})
username=$(sed -ne "s/ *'username'              => '\(.*\)',.*/\1/p"                             ${configfile})
password=$(sed -ne "s/ *'password'              => '\(.*\)',.*/\1/p"                             ${configfile})

# Only use the password option if a password has been specified
if [ -n "${password}" ]; then
    password="-p${password}"
fi

mysqldump -u${username} ${password} --skip-comments --single-transaction --skip-opt --disable-keys --no-create-info \
          --skip-triggers --no-autocommit ${db} > tests/_data/data.sql
mysqldump -u${username} ${password} --skip-comments --single-transaction --skip-add-drop-table --no-data --skip-triggers ${db} > docs/schema.sql
mysqldump -u${username} ${password} --skip-comments --triggers --no-create-info --no-data --no-create-db --skip-opt ${db} > docs/triggers.sql
