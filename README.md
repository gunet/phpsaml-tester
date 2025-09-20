A proof of concept installation of the [php-saml](https://github.com/SAML-Toolkits/php-saml/) toolkit

* Build: `docker build -t gunet/simple-php-saml-sp .`
* Run: `docker run --rm -p 80:80 --name saml-php gunet/simple-php-saml-sp`
* Docker compose: `docker compose up -d`
  - Includes a [simple-ldap](https://hub.docker.com/r/gunet/simple-ldap) and [simple-cas](https://hub.docker.com/r/gunet/simple-cas) stack. For things to work correctly we volume mount `cas-services` into `/etc/cas/services` in order to enable only a SAML service with our own metadata
  - You need to have `host.docker.internal` point to `127.0.0.1` for things to work
  - Go to `http://host.docker.internal`
* Metadata endpoint: `http://localhost/endpoints/metadata.php`
* The docker entrypoint will make sure to download the `IDP_METADATA` and parse these in order to create the `idp` array in `idp_config.php` (which is included by `settings.php`). Otherwise you will have to find a way (volume mount) to add your own configuration

# Environment Variables
* `SP_ENTITYID=http://localhost`
* `DEBUG_MODE=true`
* `IDP_METADATA=https://sso/idp/metadata`
* `SP_SIGN_AUTHNREQUEST=false`
* `SP_WANT_MESSAGE_SIGNED=false`
* `SP_WANT_ASSERTIONS_ENCRYPTED=false`
* `SP_WANT_ASSERTIONS_SIGNED=false`