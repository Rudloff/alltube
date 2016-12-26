{include file='inc/head.tpl'}
<div class="wrapper">
    <div class="main">
        {include file="inc/logo.tpl"}
        <h2>This video is protected</h2>
        <p>You need a password in order to download this video.</p>
        <form action="" method="POST">
            <input class="URLinput" type="password" name="password" title="Video password" />
            <br/><br/>
            <input class="downloadBtn" type="submit" value="Download" />
        </form>
    </div>
{include file='inc/footer.tpl'}
