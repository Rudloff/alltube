<!doctype html>
<html lang="{$locale->getLocale()->getBcp47()}">
{include file='inc/head.tpl'}
<body>
<div class="page {$class}">
    {include file='inc/header.tpl'}
    <div class="wrapper">
        <main class="main">
            {block name="main"}{/block}
        </main>
    </div>
    {include file='inc/footer.tpl'}
</div>
{if isset($debug_render)}
    {$debug_render->render()}
{/if}
</body>
</html>
