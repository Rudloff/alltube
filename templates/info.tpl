{extends file='page.tpl'}
{block name='main'}
    <div itemscope itemtype="https://schema.org/VideoObject">
        {include file="inc/logo.tpl"}
        {include file='snippets/title.tpl' assign=title}
        <p id="download_intro">
            {t params=['@title' => $title]}You are going to download @title.{/t}
        </p>
        {if isset($video->thumbnail)}
            {html_image file=$video->thumbnail itemprop="thumbnailUrl" class="thumb"}
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
                {*
                To make the default generic formats translatable:
                {t}Best{/t}
                {t}Remux best video with best audio{/t}
                {t}Worst{/t}
                *}
                {html_options name='format' options=$formats selected=$defaultFormat id="format" class="formats monospace"}
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
                {html_options name='customFormat' values=$config->convertAdvancedFormats output=$config->convertAdvancedFormats
                title="{t}Custom format{/t}" name="customFormat" aria-label="{t}Format to convert to{/t}"}
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
    </div>
{/block}
