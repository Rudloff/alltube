<head>
    <meta charset="UTF-8"/>
    <meta name=viewport content="width=device-width, initial-scale=1"/>
    {if isset($description)}
        <meta name="description" content="{$description|escape}"/>
        <meta name="twitter:description" content="{$description|escape}"/>
        <meta property="og:description" content="{$description|escape}"/>
    {/if}
    <link rel="stylesheet" href="{base_url}/assets/open-sans/open-sans.css"/>
    <link rel="stylesheet" href="{base_url}/css/style.css"/>
    <title>{$config->appName}{if isset($title)} - {$title|escape}{/if}</title>
    <link rel="icon" href="{base_url}/img/favicon.png"/>
    <meta property="og:title" content="{$config->appName}{if isset($title)} - {$title|escape}{/if}"/>
    <meta property="og:image" content="{base_url}/img/logo.png"/>
    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:title" content="{$config->appName}{if isset($title)} - {$title|escape}{/if}"/>
    <meta name="twitter:image" content="{base_url}/img/logo.png"/>
    <meta name="twitter:creator" content="@Tael67"/>
    <meta name="theme-color" content="#4F4F4F"/>
    <link rel="manifest" href="{base_url}/resources/manifest.json"/>
    <meta name="generator" content="AllTube Download ({$config->getAppVersion()})"/>

    {if isset($debug_render)}
        {$debug_render->renderHead()}
    {/if}
</head>
