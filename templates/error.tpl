{include file='inc/head.tpl'}
<div class="wrapper">
    <main class="main error">
        {include file="inc/logo.tpl"}
        <h2>{t}An error occurred{/t}</h2>
        {t}Please check the URL of your video.{/t}
        <p><i>{$error|escape}</i></p>
    </main>
    {include file='inc/footer.tpl'}
