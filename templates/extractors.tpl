{extends file='page.tpl'}
{block name='main'}
    {include file='inc/logo.tpl'}
    <h2 class="titre">{t}Supported websites{/t}</h2>
    <div class="tripleliste">
        <ul>
            {foreach $extractors as $extractor}
                <li>{$extractor}</li>
            {/foreach}
        </ul>
    </div>
{/block}
