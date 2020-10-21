{include file="inc/head.tpl"}
<div class="wrapper">
    <div itemscope itemtype="http://schema.org/VideoObject">
        <main class="main">
            {include file="inc/logo.tpl"}
            {$title="<i itemprop='name'>
    <a itemprop='url' id='video_link'
        href='{$video->webpage_url}'>
        {$video->title}</a></i>"}
            <p id="download_intro">
                {t params=['@title' => $title]}You are going to download @title.{/t}
            </p>
            {if isset($video->thumbnail)}
                <img itemprop="thumbnailUrl" class="thumb" src="{$video->thumbnail}" alt=""/>
            {/if}
            {if isset($video->description)}
                <meta itemprop="description" content="{$video->description|escape}"/>
            {/if}
            {if isset($video->upload_date)}
                <meta itemprop="uploadDate" content="{$video->upload_date}"/>
            {/if}
            <br/>
            <form action="{path_for name="download"}">
                <input type="hidden" name="url" value="{$video->webpage_url}"/>
                {if $config->uglyUrls}
                    <input type="hidden" name="page" value="download"/>
                {/if}
                {if isset($video->formats) && count($video->formats) > 1}
                    <h3><label for="format">{t}Available formats:{/t}</label></h3>
                    <select name="format" id="format" class="formats monospace">
                        <optgroup label="{t}Generic formats{/t}">
                            {foreach $config->genericFormats as $format => $name}
                                {*
                                To make the default generic formats translatable:
                                {t}Best{/t}
                                {t}Remux best video with best audio{/t}
                                {t}Worst{/t}
                                *}
                                <option value="{$format}">{t}{$name}{/t}</option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{t}Detailed formats{/t}" class="monospace">
                            {foreach $video->formats as $format}
                                {if $config->stream || $format->protocol|in_array:array('http', 'https')}
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
                    </select>
                    <br/>
                    <br/>
                {/if}
                {if $config->stream}
                    <input type="checkbox" {if $config->stream !== 'ask'}checked{/if} name="stream" id="stream"/>
                    <label for="stream">{t}Stream the video through the server{/t}</label>
                    <br/>
                    <br/>
                {/if}
                {if $config->convertAdvanced}
                    <input type="checkbox" name="customConvert" id="customConvert"/>
                    <label for="customConvert">{t}Convert into a custom format:{/t}</label>
                    <select title="{t}Custom format{/t}" name="customFormat" aria-label="{t}Format to convert to{/t}">
                        {foreach $config->convertAdvancedFormats as $format}
                            <option>{$format}</option>
                        {/foreach}
                    </select>
                    {t}with{/t}
                    <label for="customBitrate" class="sr-only">{t}Bit rate{/t}</label>
                    <input type="number" value="{$config->audioBitrate}" title="{t}Custom bitrate{/t}"
                           class="customBitrate"
                           name="customBitrate" id="customBitrate" aria-describedby="customBitrateUnit"/>
                    <span id="customBitrateUnit">{t}kbit/s audio{/t}</span>
                    <br/>
                    <br/>
                {/if}
                <input class="downloadBtn" type="submit" value="{t}Download{/t}"/><br/>
            </form>
        </main>
    </div>
    {include file="inc/footer.tpl"}
