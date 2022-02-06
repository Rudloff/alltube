{extends file='page.tpl'}
{block name='main'}
    <div>
        {html_image file='img/logo.png' path_prefix={base_url}|cat:'/' alt=$config->appName class="logo"}
    </div>
    <form action="{path_for name="info"}">
        <label class="labelurl" for="url">
            {t}Copy here the URL of your video (YouTube, Dailymotion, etc.){/t}
        </label>
        <div class="champs">
        <span class="URLinput_wrapper">
            <!-- We used to have an autofocus attribute on this field but it triggerd a very specific CSS bug: https://github.com/Rudloff/alltube/issues/117 -->
            <input class="URLinput large-font" type="url" name="url" id="url"
                   required placeholder="https://example.com/video"/>
        </span>
            {if $config->uglyUrls}
                <input type="hidden" name="page" value="info"/>
            {/if}
            <input class="downloadBtn large-font" type="submit" value="{t}Download{/t}"/><br/>
            {if $config->convert}
                <div class="mp3 small-font">
                    <div class="mp3-inner">
                        <input type="checkbox" id="audio" class="audio"
                               name="audio" {($config->defaultAudio) ? 'checked' : ''}>
                        <label for="audio"><span class="ui"></span>
                            {t}Audio only (MP3){/t}
                        </label>
                        {if $config->convertSeek}
                            <div class="seekOptions">
                                <label for="from">{t}From{/t}</label> <input type="text"
                                                                             pattern="(\d+:)?(\d+:)?\d+(\.\d+)?"
                                                                             placeholder="HH:MM:SS" value=""
                                                                             name="from"
                                                                             id="from"/>
                                <label for="to">{t}to{/t}</label> <input type="text"
                                                                         pattern="(\d+:)?(\d+:)?\d+(\.\d+)?"
                                                                         placeholder="HH:MM:SS" value="" name="to"
                                                                         id="to"/>
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </form>
    <a class="combatiblelink small-font" href="{path_for name="extractors"}">{t}See all supported websites{/t}</a>
    <div id="bookmarklet" class="bookmarklet_wrapper">
        <p> {t}Drag this to your bookmarks bar:{/t} </p>
        <a class="bookmarklet small-font"
           href="javascript:window.location='{$domain}{path_for name='info' queryParams=['url' => '%url%']}'.replace('%url%', encodeURIComponent(location.href));">{t}Bookmarklet{/t}</a>
    </div>
{/block}
