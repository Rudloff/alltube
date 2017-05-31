<header>
    <div class="social">
        <a class="twitter" href="http://twitter.com/home?status={base_url|urlencode}" target="_blank">
            {t}Share on Twitter{/t}<div class="twittermask"></div></a>
        <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={base_url|urlencode}" target="_blank">{t}Share on Facebook{/t}<div class="facebookmask"></div></a>
    </div>
    {if isset($supportedLocales)}
        <ul class="locales">
            {foreach $supportedLocales as $supportedLocale}
                <li><a hreflang="{$supportedLocale->getBcp47()}" lang="{$supportedLocale->getBcp47()}" href="{path_for name='locale' data=['locale'=>$supportedLocale->getIso15897()]}">{$supportedLocale->getCountry()->getEmoji()} {$supportedLocale->getFullName()}</a></li>
            {/foreach}
        </ul>
    {/if}
</header>
<div class="wrapper">
