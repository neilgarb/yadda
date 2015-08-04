#! /bin/bash

curl http://localhost:8983/solr/dataimport -sS -F command=full-import -F commit=true