#! /bin/bash

curl http://localhost:8983/solr/dataimport -sS -F command=delta-import -F commit=true