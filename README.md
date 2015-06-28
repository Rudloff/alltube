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

##Config

If you want to use a custom config, you need to create a config file:

    cp config.example.php config.php


##License
This software is available under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html).

__Please use a different name and logo if you run it on a public server.__

##Other dependencies
You need [avconv](https://libav.org/avconv.html) and [rtmpdump](http://rtmpdump.mplayerhq.hu/) in order to enable conversions.
If you don't want to enable conversions, you can disable it in *config.php*.

On Debian-based systems:

    sudo apt-get install libavcodec-extra rtmpdump
