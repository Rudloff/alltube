<header>
    {if isset($supportedLocales) AND count($supportedLocales) > 1}
        <div class="locales small-font">
            <button class="localesBtn small-font" title="{t}Switch language{/t}">
                {if isset($locale)}
                    {$locale->getCountry()->getEmoji()}
                {else}
                    Set language
                {/if}
            </button>
            <ul class="supportedLocales">
                {foreach $supportedLocales as $supportedLocale}
                    {if $supportedLocale != $locale}
                        <li><a hreflang="{$supportedLocale->getBcp47()}" lang="{$supportedLocale->getBcp47()}" href="{path_for name='locale' data=['locale'=>$supportedLocale->getIso15897()]}">{$supportedLocale->getCountry()->getEmoji()} {$supportedLocale->getFullName()|ucfirst}</a></li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    {/if}
    <div class="social">
        <a class="twitter" rel="noopener" href="http://twitter.com/home?status={base_url|urlencode}" title="{t}Share on Twitter{/t}" target="_blank">
            <div class="twittermask"></div></a>
        <a class="facebook" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={base_url|urlencode}" title="{t}Share on Facebook{/t}" target="_blank"><div class="facebookmask"></div></a>
    </div>
</header>
<div class="wrapper">
