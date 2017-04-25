{include file="inc/head.tpl"}
<div class="wrapper">
<div class="main">
{include file="inc/logo.tpl"}
<p>Videos extracted from the {if isset($video->title)}<i>
    <a href="{$video->webpage_url}">
{$video->title}</a></i>{/if} playlist:
</p>
{foreach $video->entries as $video}
    <div class="playlist-entry">
        <img class="thumb" src="{$video->thumbnail}" alt="" width="200" />
        <h3><a target="_blank" href="{$video->webpage_url}">{$video->title}</a></h3>
        <a target="_blank" class="downloadBtn" href="{path_for name="redirect"}?url={$video->webpage_url}">Download</a>
        {if count($video->formats) > 1}
            <a target="_blank" href="{path_for name="video"}?url={$video->webpage_url}">More options</a>
        {/if}
    </div>
{/foreach}

</div>
{include file="inc/footer.tpl"}
