alltube
=======

HTML GUI for youtube-dl (http://alltubedownload.net/)

![Screenshot](img/screenshot.png "Alltube GUI screenshot")

##Setup
In order to get AllTube working, you need to use [npm](https://www.npmjs.com/) and [Composer](https://getcomposer.org/):

    npm install
    composer install

This will download all the required dependencies.

(Note that it will download the ffmpeg binary for 64-bits Linux. If you are on another platform, you might want to specify the path to avconv/ffmpeg in your config file.)

You should also ensure that the *templates_c* folder has the right permissions:

    chmod 777 templates_c/

If your web server is Apache, you need to set the `AllowOverride` setting to `All` or `FileInfo`.

##Config

If you want to use a custom config, you need to create a config file:

    cp config.example.yml config.yml

##Web server configuration
###Apache
You will need the following modules:

* mod_mime
* mod_rewrite

###Nginx
Here is an exemple Nginx configuration:

    server {
            server_name localhost;
            listen 443 ssl;

            root /var/www/path/to/alltube;
            index index.php;

            access_log  /var/log/nginx/alltube.access.log;
            error_log   /var/log/nginx/alltube.error.log;

            types {
                    text/html   html htm shtml;
                    text/css    css;
                    text/xml    xml;
                    application/x-web-app-manifest+json   webapp;
            }

            # Deny access to dotfiles
            location ~ /\. {
                    deny all;
            }

            location / {
                    try_files $uri /index.php?$args;
            }

            location ~ \.php$ {
                    try_files $uri /index.php?$args;

                    fastcgi_param     PATH_INFO $fastcgi_path_info;
                    fastcgi_param     PATH_TRANSLATED $document_root$fastcgi_path_info;
                    fastcgi_param     SCRIPT_FILENAME $document_root$fastcgi_script_name;

                    fastcgi_pass unix:/var/run/php5-fpm.sock;
                    fastcgi_index index.php;
                    fastcgi_split_path_info ^(.+\.php)(/.+)$;
                    fastcgi_intercept_errors off;

                    fastcgi_buffer_size 16k;
                    fastcgi_buffers 4 16k;

                    include fastcgi_params;
            }
    }


##License
This software is available under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html).

__Please use a different name and logo if you run it on a public server.__

##Other dependencies
You need [avconv](https://libav.org/avconv.html) and [rtmpdump](http://rtmpdump.mplayerhq.hu/) in order to enable conversions.
If you don't want to enable conversions, you can disable it in *config.yml*.

On Debian-based systems:

    sudo apt-get install libav-tools rtmpdump

You also probably need to edit the *avconv* variable in *config.yml* so that it points to your ffmpeg/avconv binary (*/usr/bin/avconv* on Debian/Ubuntu).
