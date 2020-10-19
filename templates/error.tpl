{include file='inc/head.tpl'}
<div class="wrapper">
    <main class="main error">
        {include file="inc/logo.tpl"}
        <h2>{t}An error occurred{/t}</h2>
        <p><i>{$error|escape|nl2br}</i></p>
    </main>
    {include file='inc/footer.tpl'}
