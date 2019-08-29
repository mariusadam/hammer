# hammer

This repository contains the following application
* api - a REST api developed in PHP using Symfony and APIPlatform
* admin - a Single Page Application which is an administrative area meaning that is provides mostly CRUD operations on the resources exposed by the api
* client - a Single Page Application which is meant to be the presentation are of this suite of applications

Other components used are
* h2-proxy - docker configuration for an HTTP2 proxy used during development to enable hot reloading during React development
* varnish - cache proxy, request to the api pass through this proxy, the api invalidates the cache when resources are updated
* mercure - allows server to push updates to clients (experimental)
