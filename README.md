alltube
=======

HTML GUI for youtube-dl (http://alltubedownload.net/)

![Screenshot](img/screenshot.png "Alltube GUI screenshot")

##Setup
In order to get AllTube working, you need to download [youtube-dl](https://rg3.github.io/youtube-dl/):

    wget https://yt-dl.org/downloads/latest/youtube-dl

You then need [npm](https://www.npmjs.com/) and [Grunt](http://gruntjs.com/):

    npm install
    grunt

You also need to create the config file:

    cp config.example.php config.php


##License
This software is available under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html).

__Please use a different name and logo if you run it on a public server.__

##Other dependencies
You need [avconv](https://libav.org/avconv.html) and [rtmpdump](http://rtmpdump.mplayerhq.hu/) in order to enable conversions.
If you don't want to enable conversions, you can disable it in *config.php*.

On Debian-based systems:

    sudo apt-get install libavcodec-extra rtmpdump
