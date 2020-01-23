## show current indices
GET /_cat/indices

## show current aliases
GET /_cat/aliases

## show current templates
GET /_cat/templates

## drop all objects
DELETE /apm-*
DELETE /metricbeat-*
DELETE /.kibana*
DELETE  /*/_aliases/*
DELETE /_template/apm-*
DELETE /_template/metricbeat-*
