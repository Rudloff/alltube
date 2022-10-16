{extends file='page.tpl'}
{block name='main'}
    <div class="error">
        {include file="inc/logo.tpl"}
        <h2>{t}An error occurred{/t}</h2>
        <p><i>{$error|escape|nl2br}</i></p>
    </div>
{/block}
