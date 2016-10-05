# Frequently asked questions

<!-- markdownlint-disable MD026 -->

## My browser plays the video. How do I download it?

Most recent browsers automatically play a video if it is a format they know how to play.
You can ususally download the video by doing *File > Save to* or *ctrl + S*.

## How do I change config parameters?

You need to create a YAML file called `config.yml` at the root of your project.
Here are the parameters that you can set:

* youtubedl: path to your youtube-dl binary
* python: path to your python binary
* params: an array of parameters to pass to youtube-dl
* curl_params: an array of parameters to pass to curl
* convert: true to enable audio conversion
* avconv: path to your avconv or ffmpeg binary
* rtmpdump: path to your rtmpdump binary

See [config.example.yml](config.example.yml) for default values.

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
