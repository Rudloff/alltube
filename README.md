# Alltube Download

HTML GUI for youtube-dl ([alltubedownload.net](http://alltubedownload.net/))

![Screenshot](img/screenshot.png "Alltube GUI screenshot")

## Setup

### From a release package

You can download the latest release package [here](https://github.com/Rudloff/alltube/releases).

You just have to unzip it on your server and it should be ready to use.

### From Git

In order to get AllTube working,
you need to use [Yarn](https://yarnpkg.com/) and [Composer](https://getcomposer.org/):

```bash
yarn install
composer install
```

This will download all the required dependencies.

(Note that it will download the ffmpeg binary for 64-bits Linux.
If you are on another platform,
you might want to specify the path to avconv/ffmpeg in your config file.)

You should also ensure that the *templates_c* folder has the right permissions:

```bash
chmod 777 templates_c/
```

If your web server is Apache,
you need to set the `AllowOverride` setting to `All` or `FileInfo`.

#### Update

When updating from Git, you need to run yarn and Composer again:

```bash
git pull
yarn install --prod
composer install
```

### On Heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

## Config

If you want to use a custom config, you need to create a config file:

```bash
cp config/config.example.yml config/config.yml
```

## PHP requirements

You will need PHP 5.5 (or higher) and the following PHP modules:

* fileinfo
* intl
* mbstring
* curl

## Web server configuration

### Apache

You will need the following modules:

* mod_mime
* mod_rewrite

### Nginx

Here is an exemple Nginx configuration:

```nginx
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
```

## Other dependencies

You need [avconv](https://libav.org/avconv.html)
in order to enable conversions.
If you don't want to enable conversions, you can disable it in `config.yml`.

On Debian-based systems:

```bash
sudo apt-get install libav-tools
```

You also probably need to edit the `avconv` variable in `config.yml`
so that it points to your ffmpeg/avconv binary (`/usr/bin/avconv` on Debian/Ubuntu).

## Use as library

Alltube can also be used as a library to extract a video URL from a webpage.

You can install it with:

```bash
composer require rudloff/alltube
```

You can then use it in your PHP code:

```php
use Alltube\Config;
use Alltube\VideoDownload;

require_once __DIR__.'/vendor/autoload.php';

$downloader = new VideoDownload(
    new Config(
        [
            'youtubedl' => '/usr/local/bin/youtube-dl',
        ]
    )
);

$downloader->getURL('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
```

The library documentation is available on [alltube.surge.sh](https://alltube.surge.sh/classes/Alltube.VideoDownload.html).

You can also have a look at this [example project](https://github.com/Rudloff/alltube-example-project).

## FAQ

Please read the [FAQ](resources/FAQ.md) before reporting any issue.

## License

This software is available under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html).

Please __use a different name and logo__ if you run it on a public server.
