# KO / Stupid stuff I did to activate php modules

Didn't want to spam the actual README, nor loose experimentations

## Admin > Tools > Site health

### KO / Some php modules are missing

Edit: Example to enable php modules already is present on [doc > Extend this image](https://hub.docker.com/r/bitnami/wordpress/) : Edit DockerFile.

---

Note: Managed to edit php.ini file inside the container, but can't restart it's php.

LATER: Fix..

No point reading below until next heading.

---

        `The optional module, imagick, is not installed, or has been disabled.`

        Docs:

        - Cf. [WP recos](https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions).
        - [Bitnami's doc on php modules](https://docs.bitnami.com/bch/apps/wordpress/configuration/install-modules-php/).

        `NOTE: Bitnami stacks already include a number of PHP modules, which are installed but not active. Before installing a new module, check that it is not already included. If it exists, simply enable it in the PHP configuration file.`

        Go check bitnami's php configuration in running container

        ```bash
        # Get container name
        docker ps

        # Go in
        docker exec -it CONTAINER_NAME bash
        # docker exec -it test-wordpress_wordpress.1.razn45iy38yoa6bk30d9r6vuc bash

        # php config file content
        >> cat /opt/bitnami/php/etc/php.ini
        ## Copy/pasted in ./bitnami-wp--default--php.ini
        ```

        Line ~886 / Search for 'Dynamic Extensions' > Activate needed extensions (dynamic)

        /!\ Some random extensions at the end of the file line ~1957

        ```ini
        # Line ~914
        ;extension=bz2
        extension=curl
        ;extension=ffi
        extension=ftp
        extension=fileinfo
        extension=gd2
        ;extension=gettext
        ;extension=gmp
        ;extension=intl
        ;extension=imap
        ;extension=ldap
        extension=mbstring
        extension=exif      ; Must be after mbstring as it depends on it
        ;extension=mysqli
        ;extension=oci8_12c  ; Use with Oracle Database 12c Instant Client
        ;extension=odbc
        extension=openssl
        ;extension=pdo_firebird
        ;extension=pdo_mysql
        ;extension=pdo_oci
        ;extension=pdo_odbc
        ;extension=pdo_pgsql
        ;extension=pdo_sqlite
        ;extension=pgsql
        ;extension=shmop

        ; The MIBS data available in the PHP distribution must be installed.
        ; See http://www.php.net/manual/en/snmp.installation.php
        ;extension=snmp

        ;extension=soap
        ;extension=sockets
        extension=sodium
        ;extension=sqlite3
        ;extension=tidy
        extension=xmlrpc
        ;extension=xsl

        # ...

        # Line ~1957
        ;extension = pdo_dblib
        ;extension = apcu
        ;extension = mcrypt
        ;extension = imagick
        ;extension = memcached
        ;extension = maxminddb
        ;extension = mongodb
        ```

        ## TESTS

        ### KO / Using DS config

        Use DS config to overload PHP configuration.

        **Attention**, the custom `php.ini` needs to be present on the host.

        ```yml
        services:
          wordpress:
            # # /!\ Only works with docker stack deploy, use bind volume for docker-compose
            configs:
              - source: wordpress_php_config
                target: '/opt/bitnami/php/etc/php.ini'
                # user specific
                uid: '1001'
                gid: '1001'
                # read-only
                mode: 0440
            volumes:
              # # Note: Use config for docker stack deploy. Bind volumes can be a security issue, and should only be used for debug through docker-compose up
              # # - '/home/singed_the_docker_peon_9f3eqk4s9/tests/nginx/curated-p8080-customUser-phpfpm--nginx.conf:/etc/nginx/nginx.conf:ro'
              # - type: bind
              #   read_only: true
              #   source: /home/singed_the_docker_peon_9f3eqk4s9/tests/wordpress/config/bitnami-wp--activate-php-modules--php.ini
              #   target: /opt/bitnami/php/etc/php.ini

        configs:
          wordpress_php_config:
            file: '/home/singed_the_docker_peon_9f3eqk4s9/tests/wordpress/config/bitnami-wp--activate-php-modules--php.ini'
        ```

        Problem: `Error executing 'postInstallation': EROFS: read-only file system, open '/opt/bitnami/php/conf/php.ini'`.

        > KO / 777
        > KO / chown 1001
        > KO / chown root
        > KO / bind instead of config

        ## KO / Docker exec as root

        No CLI text editor by default ?

        ```bash
        docker exec -it -u root test-wordpress_wordpress.1.dbl6yqi1bj4o456x6hmczojmk bash
        # still can't install
        ```

        ## ~KO / Find another text editor

        ~KO > php.ini updated, but can't find how to restart php.

        List installed packages:

        ```bash
        apt list --installed
        # Listing... Done
        # adduser/now 3.118 all [installed,local]
        # apt/now 1.8.2.1 amd64 [installed,local]
        # base-files/now 10.3+deb10u4 amd64 [installed,local]
        # base-passwd/now 3.5.46 amd64 [installed,local]
        # bash/now 5.0-4 amd64 [installed,local]
        # bsdutils/now 1:2.33.1-0.1 amd64 [installed,local]
        # ca-certificates/now 20190110 all [installed,local]
        # coreutils/now 8.30-3 amd64 [installed,local]
        # curl/now 7.64.0-4+deb10u1 amd64 [installed,local]
        # dash/now 0.5.10.2-5 amd64 [installed,local]
        # debconf/now 1.5.71 all [installed,local]
        # debian-archive-keyring/now 2019.1 all [installed,local]
        # debianutils/now 4.8.6.1 amd64 [installed,local]
        # diffutils/now 1:3.7-3 amd64 [installed,local]
        # dirmngr/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # dpkg/now 1.19.7 amd64 [installed,local]
        # fdisk/now 2.33.1-0.1 amd64 [installed,local]
        # findutils/now 4.6.0+git+20190209-2 amd64 [installed,local]
        # fontconfig-config/now 2.13.1-2 all [installed,local]
        # fonts-dejavu-core/now 2.37-1 all [installed,local]
        # freetds-common/now 1.00.104-1+deb10u1 all [installed,local]
        # gcc-8-base/now 8.3.0-6 amd64 [installed,local]
        # gnupg-l10n/now 2.2.12-1+deb10u1 all [installed,local]
        # gnupg-utils/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gnupg/now 2.2.12-1+deb10u1 all [installed,local]
        # gpg-agent/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gpg-wks-client/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gpg-wks-server/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gpg/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gpgconf/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gpgsm/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # gpgv/now 2.2.12-1+deb10u1 amd64 [installed,local]
        # grep/now 3.3-1 amd64 [installed,local]
        # gzip/now 1.9-3 amd64 [installed,local]
        # hostname/now 3.21 amd64 [installed,local]
        # imagemagick-6-common/now 8:6.9.10.23+dfsg-2.1 all [installed,local]
        # init-system-helpers/now 1.56+nmu1 all [installed,local]
        # insserv/now 1.18.0-2 amd64 [installed,local]
        # libacl1/now 2.2.53-4 amd64 [installed,local]
        # libapt-pkg5.0/now 1.8.2.1 amd64 [installed,local]
        # libassuan0/now 2.5.2-1 amd64 [installed,local]
        # libattr1/now 1:2.4.48-4 amd64 [installed,local]
        # libaudit-common/now 1:2.8.4-3 all [installed,local]
        # libaudit1/now 1:2.8.4-3 amd64 [installed,local]
        # libblkid1/now 2.33.1-0.1 amd64 [installed,local]
        # libbsd0/now 0.9.1-2 amd64 [installed,local]
        # libbz2-1.0/now 1.0.6-9.2~deb10u1 amd64 [installed,local]
        # libc-bin/now 2.28-10 amd64 [installed,local]
        # libc6/now 2.28-10 amd64 [installed,local]
        # libcap-ng0/now 0.7.9-2 amd64 [installed,local]
        # libcom-err2/now 1.44.5-1+deb10u3 amd64 [installed,local]
        # libcurl4/now 7.64.0-4+deb10u1 amd64 [installed,local]
        # libdb5.3/now 5.3.28+dfsg1-0.5 amd64 [installed,local]
        # libde265-0/now 1.0.3-1+b1 amd64 [installed,local]
        # libdebconfclient0/now 0.249 amd64 [installed,local]
        # libexpat1/now 2.2.6-2+deb10u1 amd64 [installed,local]
        # libfdisk1/now 2.33.1-0.1 amd64 [installed,local]
        # libffi6/now 3.2.1-9 amd64 [installed,local]
        # libfftw3-double3/now 3.3.8-2 amd64 [installed,local]
        # libfontconfig1/now 2.13.1-2 amd64 [installed,local]
        # libfreetype6/now 2.9.1-3+deb10u1 amd64 [installed,local]
        # libgcc1/now 1:8.3.0-6 amd64 [installed,local]
        # libgcrypt20/now 1.8.4-5 amd64 [installed,local]
        # libglib2.0-0/now 2.58.3-2+deb10u2 amd64 [installed,local]
        # libgmp10/now 2:6.1.2+dfsg-4 amd64 [installed,local]
        # libgnutls30/now 3.6.7-4+deb10u3 amd64 [installed,local]
        # libgomp1/now 8.3.0-6 amd64 [installed,local]
        # libgpg-error0/now 1.35-1 amd64 [installed,local]
        # libgssapi-krb5-2/now 1.17-3 amd64 [installed,local]
        # libheif1/now 1.3.2-2~deb10u1 amd64 [installed,local]
        # libhogweed4/now 3.4.1-1 amd64 [installed,local]
        # libicu63/now 63.1-6+deb10u1 amd64 [installed,local]
        # libidn2-0/now 2.0.5-1+deb10u1 amd64 [installed,local]
        # libjbig0/now 2.1-3.1+b2 amd64 [installed,local]
        # libjemalloc2/now 5.1.0-3 amd64 [installed,local]
        # libjpeg62-turbo/now 1:1.5.2-2+b1 amd64 [installed,local]
        # libk5crypto3/now 1.17-3 amd64 [installed,local]
        # libkeyutils1/now 1.6-6 amd64 [installed,local]
        # libkrb5-3/now 1.17-3 amd64 [installed,local]
        # libkrb5support0/now 1.17-3 amd64 [installed,local]
        # libksba8/now 1.3.5-2 amd64 [installed,local]
        # liblcms2-2/now 2.9-3 amd64 [installed,local]
        # libldap-2.4-2/now 2.4.47+dfsg-3+deb10u2 amd64 [installed,local]
        # libldap-common/now 2.4.47+dfsg-3+deb10u2 all [installed,local]
        # liblqr-1-0/now 0.4.2-2.1 amd64 [installed,local]
        # libltdl7/now 2.4.6-9 amd64 [installed,local]
        # liblz4-1/now 1.8.3-1 amd64 [installed,local]
        # liblzma5/now 5.2.4-1 amd64 [installed,local]
        # libmagickcore-6.q16-6/now 8:6.9.10.23+dfsg-2.1 amd64 [installed,local]
        # libmagickwand-6.q16-6/now 8:6.9.10.23+dfsg-2.1 amd64 [installed,local]
        # libmcrypt4/now 2.5.8-3.4 amd64 [installed,local]
        # libmemcached11/now 1.0.18-4.2 amd64 [installed,local]
        # libmemcachedutil2/now 1.0.18-4.2 amd64 [installed,local]
        # libmount1/now 2.33.1-0.1 amd64 [installed,local]
        # libncurses6/now 6.1+20181013-2+deb10u2 amd64 [installed,local]
        # libncursesw6/now 6.1+20181013-2+deb10u2 amd64 [installed,local]
        # libnettle6/now 3.4.1-1 amd64 [installed,local]
        # libnghttp2-14/now 1.36.0-2+deb10u1 amd64 [installed,local]
        # libnpth0/now 1.6-1 amd64 [installed,local]
        # libnuma1/now 2.0.12-1 amd64 [installed,local]
        # libonig5/now 6.9.1-1 amd64 [installed,local]
        # libopenjp2-7/now 2.3.0-2+deb10u1 amd64 [installed,local]
        # libp11-kit0/now 0.23.15-2 amd64 [installed,local]
        # libpam-modules-bin/now 1.3.1-5 amd64 [installed,local]
        # libpam-modules/now 1.3.1-5 amd64 [installed,local]
        # libpam-runtime/now 1.3.1-5 all [installed,local]
        # libpam0g/now 1.3.1-5 amd64 [installed,local]
        # libpcre3/now 2:8.39-12 amd64 [installed,local]
        # libpng16-16/now 1.6.36-6 amd64 [installed,local]
        # libpq5/now 11.7-0+deb10u1 amd64 [installed,local]
        # libprocps7/now 2:3.3.15-2 amd64 [installed,local]
        # libpsl5/now 0.20.2-2 amd64 [installed,local]
        # libreadline7/now 7.0-5 amd64 [installed,local]
        # librtmp1/now 2.4+20151223.gitfa8646d.1-2 amd64 [installed,local]
        # libsasl2-2/now 2.1.27+dfsg-1+deb10u1 amd64 [installed,local]
        # libsasl2-modules-db/now 2.1.27+dfsg-1+deb10u1 amd64 [installed,local]
        # libseccomp2/now 2.3.3-4 amd64 [installed,local]
        # libselinux1/now 2.8-1+b1 amd64 [installed,local]
        # libsemanage-common/now 2.8-2 all [installed,local]
        # libsemanage1/now 2.8-2 amd64 [installed,local]
        # libsepol1/now 2.8-1 amd64 [installed,local]
        # libsmartcols1/now 2.33.1-0.1 amd64 [installed,local]
        # libsqlite3-0/now 3.27.2-3 amd64 [installed,local]
        # libssh2-1/now 1.8.0-2.1 amd64 [installed,local]
        # libssl1.1/now 1.1.1d-0+deb10u3 amd64 [installed,local]
        # libstdc++6/now 8.3.0-6 amd64 [installed,local]
        # libsybdb5/now 1.00.104-1+deb10u1 amd64 [installed,local]
        # libsystemd0/now 241-7~deb10u4 amd64 [installed,local]
        # libtasn1-6/now 4.13-3 amd64 [installed,local]
        # libtidy5deb1/now 2:5.6.0-10 amd64 [installed,local]
        # libtiff5/now 4.1.0+git191117-2~deb10u1 amd64 [installed,local]
        # libtinfo6/now 6.1+20181013-2+deb10u2 amd64 [installed,local]
        # libudev1/now 241-7~deb10u4 amd64 [installed,local]
        # libunistring2/now 0.9.10-1 amd64 [installed,local]
        # libuuid1/now 2.33.1-0.1 amd64 [installed,local]
        # libwebp6/now 0.6.1-2 amd64 [installed,local]
        # libwebpmux3/now 0.6.1-2 amd64 [installed,local]
        # libx11-6/now 2:1.6.7-1 amd64 [installed,local]
        # libx11-data/now 2:1.6.7-1 all [installed,local]
        # libx265-165/now 2.9-4 amd64 [installed,local]
        # libxau6/now 1:1.0.8-1+b2 amd64 [installed,local]
        # libxcb1/now 1.13.1-2 amd64 [installed,local]
        # libxdmcp6/now 1:1.1.2-3 amd64 [installed,local]
        # libxext6/now 2:1.3.3-1+b2 amd64 [installed,local]
        # libxml2/now 2.9.4+dfsg1-7+b3 amd64 [installed,local]
        # libxslt1.1/now 1.1.32-2.2~deb10u1 amd64 [installed,local]
        # libzip4/now 1.5.1-4 amd64 [installed,local]
        # libzstd1/now 1.3.8+dfsg-3 amd64 [installed,local]
        # login/now 1:4.5-1.1 amd64 [installed,local]
        # lsb-base/now 10.2019051400 all [installed,local]
        # mawk/now 1.3.3-17+b3 amd64 [installed,local]
        # mount/now 2.33.1-0.1 amd64 [installed,local]
        # ncurses-base/now 6.1+20181013-2+deb10u2 all [installed,local]
        # openssl/now 1.1.1d-0+deb10u3 amd64 [installed,local]
        # passwd/now 1:4.5-1.1 amd64 [installed,local]
        # perl-base/now 5.28.1-6 amd64 [installed,local]
        # pinentry-curses/now 1.1.0-2 amd64 [installed,local]
        # procps/now 2:3.3.15-2 amd64 [installed,local]
        # readline-common/now 7.0-5 all [installed,local]
        # sed/now 4.7-1 amd64 [installed,local]
        # sensible-utils/now 0.0.12 all [installed,local]
        # startpar/now 0.61-1 amd64 [installed,local]
        # sudo/now 1.8.27-1+deb10u2 amd64 [installed,local]
        # sysv-rc/now 2.93-8 all [installed,local]
        # sysvinit-utils/now 2.93-8 amd64 [installed,local]
        # tar/now 1.30+dfsg-6 amd64 [installed,local]
        # tzdata/now 2020a-0+deb10u1 all [installed,local]
        # ucf/now 3.0038+nmu1 all [installed,local]
        # unzip/now 6.0-23+deb10u1 amd64 [installed,local]
        # util-linux/now 2.33.1-0.1 amd64 [installed,local]
        # zlib1g/now 1:1.2.11.dfsg-1 amd64 [installed,local]
        ```

        At least imagemagick-6-common is already installed.

        sed permet d'[éditer des fichiers](https://debian-facile.org/doc:systeme:sed#option-e) de manière archaique.

        ```bash
        sed
        # Yep, installed

        # Regexp replace
        # sed -e "s/abricots/myrtilles/g" test1.txt
        # OK / Replace ';extension=curl' with 'extension=curl'
        sed -e "s/;extension=curl/extension=curl/g" /opt/bitnami/php/etc/php.ini
        ```

        SOOOOOoooooooo, let's activate all the wanted php modules

        ```bash
        # OK
        sed \
          -e "s/;extension=curl/extension=curl/g" \
          -e "s/;extension=ftp/extension=ftp/g" \
          -e "s/;extension=fileinfo/extension=fileinfo/g" \
          -e "s/;extension=gd2/extension=gd2/g" \
          -e "s/;extension=mbstring/extension=mbstring/g" \
          -e "s/;extension=exif/extension=exif/g" \
          -e "s/;extension=openssl/extension=openssl/g" \
          -e "s/;extension=sodium/extension=sodium/g" \
          -e "s/;extension=xmlrpc/extension=xmlrpc/g" \
          -e "s/;extension = imagick/extension = imagick/g" \
          -e "s/;extension = memcached/extension = memcached/g" \
          /opt/bitnami/php/etc/php.ini
        ```

        Is it really dynamic, or [php restart](https://docs.bitnami.com/aws/faq/administration/control-services/) needed ?

        ```bash
        # lel, ofc needs php reboot

        # KO / No sudo nor ctlscript.sh
        sudo /opt/bitnami/ctlscript.sh restart apache

        # IMO it will probably break on container reboot anyway
        ```

---

### REST API error

Seems ok when [accessing manually](https://test-wordpress.masamune.fr/wp-json/wp/v2/users/1).

LATER: This seems time-consuming, lots of stuff to check. Fix later.
