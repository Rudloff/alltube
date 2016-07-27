<div class="wrapper">
    <div class="main error">
    {include file="logo.tpl"}
    <h2>An error occured</h2>
    Please check the URL of your video.
    <p><i>
    {foreach $errors as $error}
        {$error|escape}
        <br/>
    {/foreach}
    </i></p>
    </div>
