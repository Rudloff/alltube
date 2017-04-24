{include file="inc/head.tpl"}
<div class="wrapper">
<div class="main">
{include file="inc/logo.tpl"}
<p>Videos extracted from the<i>
    <a href="{$video->webpage_url}">
{$video->title}</a></i> playlist:
</p>
{foreach $video->entries as $video}
    <div class="playlist-entry">
        <img class="thumb" src="{$video->thumbnail}" alt="" width="200" />
        <h3><a href="{$video->webpage_url}">{$video->title}</a></h3>
        <a class="downloadBtn" href="{path_for name="redirect"}?url={$video->webpage_url}">Download</a>
    </div>
{/foreach}

</div>
</div>
{include file="inc/footer.tpl"}
