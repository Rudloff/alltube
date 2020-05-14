{include file="inc/head.tpl"}
<div class="wrapper">
    <main class="main">
        {include file="inc/logo.tpl"}

        {if isset($video->title)}
            {$title="<i>
        <a href='{$video->webpage_url}'>
            {$video->title}</a>
    </i>"}
            <p>
                {t params=['@title'=>$title]}Videos extracted from @title:{/t}
            </p>
        {/if}

        {if $config->stream}
            <a href="{path_for name="download"}?url={$video->webpage_url}" class="downloadBtn">Download everything</a>
        {/if}
        {foreach $video->entries as $entry}
            <div class="playlist-entry">
                <h3 class="playlist-entry-title"><a target="_blank" href="{strip}
                {if isset($entry->ie_key) and $entry->ie_key == Youtube and !filter_var($entry->url, FILTER_VALIDATE_URL)}
                    https://www.youtube.com/watch?v=
                {/if}
                {$entry->url}
            {/strip}">
                        {if !isset($entry->title)}
                            {if $entry->ie_key == YoutubePlaylist}
                                Playlist
                            {else}
                                Video
                            {/if}
                        {else}
                            {$entry->title}
                        {/if}
                    </a></h3>
                <a target="_blank" class="downloadBtn"
                   href="{path_for name="download"}?url={$entry->url}">{t}Download{/t}</a>
                <a target="_blank" href="{path_for name="info"}?url={$entry->url}">{t}More options{/t}</a>
            </div>
        {/foreach}

    </main>
    {include file="inc/footer.tpl"}
