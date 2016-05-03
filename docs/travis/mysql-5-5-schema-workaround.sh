#!/bin/bash
# Removes the DEFAULT CURRENT_TIMESTAMP from `created` in the schema
# for mysql 5.5 because on travis 5.6 isn't available yet

# cd into the git root
cd $(git rev-parse --show-toplevel)

sed -i 's/`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP/`created` timestamp NOT NULL DEFAULT '"'"'2000-01-01 00:00:00'"'"'/g' docs/schema.sql
sed -i 's/`modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP/`modified` timestamp NOT NULL DEFAULT '"'"'2000-01-01 00:00:00'"'"'/g' docs/schema.sql
