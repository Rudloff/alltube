<header>
    {if isset($supportedLocales) AND count($supportedLocales) > 1}
        <div class="locales small-font">
            <button class="localesBtn small-font" title="{t}Switch language{/t}">
                {if isset($locale) AND $locale->getCountry()}
                    {$locale->getCountry()->getEmoji()}
                {else}
                    {t}Set language{/t}
                {/if}
            </button>
            <ul class="supportedLocales">
                {foreach $supportedLocales as $supportedLocale}
                    {if $supportedLocale != $locale}
                        <li>
                            <a hreflang="{$supportedLocale->getBcp47()}"
                               lang="{$supportedLocale->getBcp47()}"
                               href="{path_for name='locale' data=['locale'=>$supportedLocale->getIso15897()]}">
                                {if $supportedLocale->getCountry()}
                                    {$supportedLocale->getCountry()->getEmoji()}
                                {/if}
                                {$supportedLocale->getFullName()|ucfirst}
                            </a>
                        </li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    {/if}
</header>
<div class="wrapper">
