{include file="inc/head.tpl"}
<div class="wrapper">
<main class="main">
{include file="inc/logo.tpl"}
<p>{t}Videos extracted from{/t} {if isset($video->title)}<i>
    <a href="{$video->webpage_url}">
{$video->title}</a></i>{/if}{t}:{/t}
</p>
{if $config->stream}
    <a href="{path_for name="redirect"}?url={$video->webpage_url}" class="downloadBtn">Download everything</a>
{/if}
{foreach $video->entries as $video}
    <div class="playlist-entry">
        <h3 class="playlist-entry-title"><a target="_blank" href="{strip}
                {if isset($video->ie_key) and $video->ie_key == Youtube and !filter_var($video->url, FILTER_VALIDATE_URL)}
                    https://www.youtube.com/watch?v=
                {/if}
                {$video->url}
            {/strip}">
            {if !isset($video->title) and $video->ie_key == YoutubePlaylist}
                Playlist
            {else}
                {$video->title}
            {/if}
        </a></h3>
        <a target="_blank" class="downloadBtn" href="{path_for name="redirect"}?url={$video->url}">{t}Download{/t}</a>
        <a target="_blank" href="{path_for name="video"}?url={$video->url}">{t}More options{/t}</a>
    </div>
{/foreach}

</main>
{include file="inc/footer.tpl"}
