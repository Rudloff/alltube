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
    src="{base_url|noscheme}/img/ic_media_route_disabled_holo_light.png"
    alt="Google Cast™ is disabled"
    title="Google Cast is not supported on this browser." />
<img class="cast_btn cast_hidden cast_icon" id="cast_btn_launch"
    src="{base_url|noscheme}/img/ic_media_route_off_holo_light.png"
    title="Cast to ChromeCast" alt="Google Cast™" />
<img src="{base_url|noscheme}/img/ic_media_route_on_holo_light.png"
    alt="Casting to ChromeCast…" title="Stop casting"
    id="cast_btn_stop" class="cast_btn cast_hidden cast_icon" /></p>
{if isset($video->thumbnail)}
    <img itemprop="image" class="thumb" src="{$video->thumbnail}" alt="" />
{/if}
<br/>
{if isset($video->formats)}
    <h3><label for="format">Available formats:</label></h3>
    <form action="{path_for name="redirect"}">
        <input type="hidden" name="url" value="{$video->webpage_url}" />
        <select name="format" id="format">
            <option value="best">
                Best ({$video->ext})
            </option>
            <optgroup>
                {foreach $video->formats as $format}
                    <option value="{$format->format_id}">
                        {$format->format} ({$format->ext})
                    </option>
                {/foreach}
            </optgroup>
        </select><br/><br/>
        <input class="downloadBtn" type="submit" value="Download" /><br/>
    </form>
{else}
    <input type="hidden" name="format" value="best" />
    <a class="downloadBtn"
        href="{$video->url|escape}">Download</a><br/>
{/if}
</div>
</div>
