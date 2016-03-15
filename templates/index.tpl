    <div class="main">
    <div><img itemprop="image" class="logo" src="{siteUrl|noscheme|replace:'index.php':'' url='img/logo.png'}"
    alt="AllTube Download" width="328" height="284"></div>
    <form action="{urlFor name="video"}">
    <label class="labelurl" for="url">
        Copy here the URL of your video (Youtube, Dailymotion, etc.)
    </label>
    <div class="champs">
        <span class="URLinput_wrapper">
        <input class="URLinput" type="url" name="url" id="url"
        required autofocus placeholder="http://example.com/video" />
        </span>
        <input class="downloadBtn" type="submit" value="Download" /><br/>
        {if $convert}
            <div class="mp3">
                <p><input type="checkbox" id="audio" class="audio" name="audio">
                <label for="audio"><span class="ui"></span>
                    Audio only (MP3)</label></p>
            </div>
        {/if}
    </div>
    </form>
    <a class="combatiblelink" href="{urlFor name="extractors"}">See all supported websites</a>
    <div id="bookmarklet">
        <p> Drag this to your bookmarks bar: </p>
        <a class="bookmarklet" href="javascript:window.location='{siteUrl withUri=false}{urlFor name='video'}?url='+encodeURIComponent(location.href);">Bookmarklet</a>
    </div>

</div>
