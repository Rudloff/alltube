# Frequently asked questions

<!-- markdownlint-disable MD026 -->

## My browser plays the video. How do I download it?

Most recent browsers automatically play a video if it is a format they know how to play.
You can ususally download the video by doing *File > Save to* or *ctrl + S*.

## How do I change config parameters?

You need to create a YAML file called `config.yml` at the root of your project.
Here are the parameters that you can set:

* `youtubedl`: path to your youtube-dl binary
* `python`: path to your python binary
* `params`: an array of parameters to pass to youtube-dl
* `curl_params`: an array of parameters to pass to curl
* `convert`: true to enable audio conversion
* `avconv`: path to your avconv or ffmpeg binary
* `rtmpdump`: path to your rtmpdump binary

See [`config.example.yml`](config.example.yml) for default values.

## How do I enable audio conversion?

In order to enable audio conversion, you need to add this to your `config.yml` file:

```yaml
convert: true
avconv: path/to/avconv
```

You will also need to install `avconv` and `curl` on your server:

```bash
sudo apt-get install libav-tools curl
```

## How do I deploy Alltube on Heroku?

Create a dyno with the following buildpacks:

* `heroku/php`
* `heroku/nodejs`
* `heroku/python`

You might also need to add the following config variables:

```env
CONVERT=1
PYTHON=/app/.heroku/python/bin/python
```

Then push the code to Heroku and it should work out of the box.

## Why can't I download videos from some websites (e.g. Dailymotion)

Some websites generate an unique video URL for each IP address. When using Alltube, the URL is generated for our server's IP address and your computer is not allowed to use it.

There are two known workarounds:

* You can run Alltube locally on your computer.
* You can use the experimental `feature/stream` branch which streams the video through the server in order to bypass IP restrictions.
  Please note that this can use a lot of resources on the server (which is why we won't enable it on alltubedownload.net).

## CSS and JavaScript files are missing

You probably don't have the minified files (in the `dist` folder).
You need to either:

* Use a [release package](https://github.com/Rudloff/alltube/releases)
* Run `npm install` (see detailed instructions in the [README](README.md#from-git))

## I get a 404 error on every page except the index

This is probably because your server does not have mod_rewrite or AllowOverride is disabled.
You can work around this by adding this to your `config.yml` file:

```yaml
uglyUrls: true
```
