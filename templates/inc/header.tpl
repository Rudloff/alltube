<header>
    <div class="social">
        <a class="twitter" href="http://twitter.com/home?status={base_url|urlencode}" target="_blank">
            {t}Share on Twitter{/t}<div class="twittermask"></div></a>
        <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={base_url|urlencode}" target="_blank">{t}Share on Facebook{/t}<div class="facebookmask"></div></a>
    </div>
    {if isset($supportedLocales)}
    <div class="locales">
        <button class="localesBtn" title="{t}Switch language{/t}">{$locale->getCountry()->getEmoji()}</button>
        <ul class="supportedLocales">
            {foreach $supportedLocales as $supportedLocale}
                {if $supportedLocale != $locale}
                    <li><a hreflang="{$supportedLocale->getBcp47()}" lang="{$supportedLocale->getBcp47()}" href="{path_for name='locale' data=['locale'=>$supportedLocale->getIso15897()]}">{$supportedLocale->getCountry()->getEmoji()} {$supportedLocale->getFullName()}</a></li>
                {/if}
            {/foreach}
        </ul>
    {/if}
</div>
</header>
<div class="wrapper">
