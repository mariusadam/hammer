# hammer

This repository contains the following application
* `api` - a REST api developed in PHP using Symfony and APIPlatform
* `admin` - a Single Page Application which is an administrative area meaning that is provides mostly CRUD operations on the resources exposed by the api
* `client` - a Single Page Application which is meant to be the presentation are of this suite of applications

Docker containers overview
* `php` - hosts the PHP runtime
* `api` - web-server which passes script execution to php container on port 9000
* `db` - PostgreSQL
* `admin` - used during development of the admin app
* `client` - used during development of the client app
* `h2-proxy` - an HTTP2 proxy used during development to enable hot reloading during React development
* `varnish` - cache proxy, request to the api pass through this proxy, the api invalidates the cache when resources are updated
* `mercure` - allows server to push updates to clients (experimental)
