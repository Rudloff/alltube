{include file="inc/head.tpl"}
<div class="wrapper">
<div itemscope itemtype="http://schema.org/VideoObject">
<div class="main">
{include file="inc/logo.tpl"}
<p id="download_intro">You are going to download<i itemprop="name">
    <a itemprop="url" id="video_link"
        data-ext="{$video->ext}"
        data-video="{$video->url|escape}"
        href="{$video->webpage_url}">
{$video->title}</a></i>.
</p>
{if isset($video->thumbnail)}
    <img itemprop="thumbnailUrl" class="thumb" src="{$video->thumbnail}" alt="" />
{/if}
{if isset($video->description)}
    <meta itemprop="description" content="{$video->description|escape}" />
{/if}
{if isset($video->upload_date)}
    <meta itemprop="uploadDate" content="{$video->upload_date}" />
{/if}
<br/>
{if isset($video->formats)}
    <h3><label for="format">Available formats:</label></h3>
    <form action="{path_for name="redirect"}">
        <input type="hidden" name="url" value="{$video->webpage_url}" />
        <select name="format" id="format" class="formats monospace">
            <optgroup label="Generic formats">
                <option value="best[protocol^=http]">
                    {strip}
                        Best ({$video->ext}
                        {if isset($video->filesize)}
                            {$video->filesize}
                        {/if}
                        )
                    {/strip}
                </option>
                <option value="worst[protocol^=http]">
                    Worst
                </option>
            </optgroup>
            <optgroup label="Detailed formats" class="monospace">
                {foreach $video->formats as $format}
                    {if $format->protocol|in_array:array('http', 'https')}
                        {strip}
                        <option value="{$format->format_id}">
                            {$format->ext}
                            {for $foo=1 to (5 - ($format->ext|strlen))}
                                &nbsp;
                            {/for}
                            {if isset($format->width)}
                                {$format->width}x{$format->height}
                                {for $foo=1 to (10 - (("{$format->width}x{$format->height}")|strlen))}
                                    &nbsp;
                                {/for}
                            {else}
                                {for $foo=1 to 10}
                                    &nbsp;
                                {/for}
                            {/if}
                            {if isset($format->filesize)}
                                {($format->filesize/1000000)|round:2} MB
                                {for $foo=1 to (7 - (($format->filesize/1000000)|round:2|strlen))}
                                    &nbsp;
                                {/for}
                            {else}
                                {for $foo=1 to 10}
                                    &nbsp;
                                {/for}
                            {/if}
                            {if isset($format->format_note)}
                                {$format->format_note}
                            {/if}
                            &nbsp;({$format->format_id})
                        </option>
                        {/strip}
                    {/if}
                {/foreach}
            </optgroup>
        </select><br/><br/>
        <input class="downloadBtn" type="submit" value="Download" /><br/>
    </form>
{else}
    <input type="hidden" name="format" value="best[protocol^=http]" />
    <a class="downloadBtn"
        href="{$video->url|escape}">Download</a><br/>
{/if}
</div>
</div>
{include file="inc/footer.tpl"}
