<div class="wrapper">
<div itemscope itemtype="http://schema.org/VideoObject">
<div class="main">
{include file="logo.tpl"}
<p>You are going to download<i itemprop="name">
    <a itemprop="url" id="video_link"
        data-ext="{$video->ext}"
        data-video="{$video->url|escape}"
        href="{$video->webpage_url}">
{$video->title}</a></i>.
<img class="cast_icon" id="cast_disabled"
    src="{siteUrl|noscheme url='img/ic_media_route_disabled_holo_light.png'}"
    alt="Google Cast™ is disabled"
    title="Google Cast is not supported on this browser." />
<img class="cast_btn cast_hidden cast_icon" id="cast_btn_launch"
    src="{siteUrl|noscheme url='img/ic_media_route_off_holo_light.png'}"
    title="Cast to ChromeCast" alt="Google Cast™" />
<img src="{siteUrl|noscheme url='img/ic_media_route_on_holo_light.png'}"
    alt="Casting to ChromeCast…" title="Stop casting"
    id="cast_btn_stop" class="cast_btn cast_hidden cast_icon" /></p>
{if isset($video->thumbnail)}
    <img itemprop="image" class="thumb" src="{$video->thumbnail}" alt="" />
{/if}
<br/>
<input type="hidden" name="url"
value="{$video->webpage_url}" />
{if isset($video->formats)}
    <h3>Available formats:</h3>
    <p>(You might have to do a <i>Right click > Save as</i>)</p>
    <ul id="format" class="format">
    <li class="best" itemprop="encoding" itemscope
    itemtype="http://schema.org/VideoObject">
    <a download="{$video->_filename}" itemprop="contentUrl"
        href="{$video->url|escape}">
    <b>Best</b> (<span itemprop="encodingFormat">{$video->ext}</span>)
    </a></li>
    {foreach $video->formats as $format}
        <li itemprop="encoding"
            itemscope itemtype="http://schema.org/VideoObject">
        <a download="{$video->_filename|replace:$video->ext:$format->ext}" itemprop="contentUrl"
            href="{$format->url|escape}">
        <span itemprop="videoQuality">{$format->format}</span> (<span itemprop="encodingFormat">{$format->ext}</span>)
        </a></li>
    {/foreach}
    </ul><br/><br/>
{else}
    <input type="hidden" name="format" value="best" />
    <a class="downloadBtn"
        href="{$video->url|escape}">Download</a><br/>
{/if}
</div>
</div>
