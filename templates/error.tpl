{include file='inc/head.tpl'}
<div class="wrapper">
    <div class="main error">
    {include file="inc/logo.tpl"}
    <h2>{t}An error occurred{/t}</h2>
    {t}Please check the URL of your video.{/t}
    <p><i>
    {foreach $errors as $error}
        {$error|escape}
        <br/>
    {/foreach}
    </i></p>
</div>
{include file='inc/footer.tpl'}
