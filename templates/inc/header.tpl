<header>
    <div class="social">
        <a class="twitter" href="http://twitter.com/home?status={base_url|urlencode}" target="_blank">
            {t}Share on Twitter{/t}<div class="twittermask"></div></a>
        <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={base_url|urlencode}" target="_blank">{t}Share on Facebook{/t}<div class="facebookmask"></div></a>
    </div>
    <ul class="locales">
        {if isset($locales)}
            {foreach $locales as $supportedLocale}
                <li><a href="{path_for name='locale' data=['locale'=>$supportedLocale->getIso15897()]}"><span class="flag-icon flag-icon-{$supportedLocale->getIso3166()}"></span> {$supportedLocale->getFullName()}</a></li>
            {/foreach}
        {/if}
    </ul>
</header>
<div class="wrapper">
