{include file='inc/head.tpl'}
{include file='inc/header.tpl'}
{locale path="Translations" domain="Alltube"}
<div class="main">
    <div><img class="logo" src="{base_url}/img/logo.png"
    alt="AllTube Download" width="328" height="284"></div>
    <form action="{path_for name="video"}">
    <label class="labelurl" for="url">
        {t}Copy here the URL of your video (Youtube, Dailymotion, etc.){/t}
    </label>
    <div class="champs">
        <span class="URLinput_wrapper">
        <input class="URLinput" type="url" name="url" id="url"
        required autofocus placeholder="http://example.com/video" />
        </span>
        {if $uglyUrls}
            <input type="hidden" name="page" value="video" />
        {/if}
        <input class="downloadBtn" type="submit" value="{t}Download{/t}" /><br/>
        {if $convert}
            <div class="mp3">
                <p><input type="checkbox" id="audio" class="audio" name="audio">
                <label for="audio"><span class="ui"></span>
                    {t}Audio only (MP3){/t}</label></p>
            </div>
        {/if}
    </div>
    </form>
    <a class="combatiblelink" href="{path_for name="extractors"}">{t}See all supported websites{/t}</a>
    <div id="bookmarklet" class="bookmarklet_wrapper">
        <p> {t}Drag this to your bookmarks bar:{/t} </p>
        <a class="bookmarklet" href="javascript:window.location='{$domain}{path_for name='video'}?url='+encodeURIComponent(location.href);">{t}Bookmarklet{/t}</a>
    </div>

</div>
{include file='inc/footer.tpl'}
