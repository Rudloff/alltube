</div>
<footer class="small-font">
    <div class="footer_wrapper">
        {$dev="<a rel='author' target='blank'
            href='http://rudloff.pro/'>
            Pierre Rudloff
        </a>"}
        {t params=['@dev'=>$dev]}Code by @dev{/t}

        &middot;

        {$designer="<a rel='author' target='blank'
            href='http://olivierhaquette.fr'>
            Olivier Haquette
        </a>"}
        {t params=['@designer' => $designer]}Design by @designer{/t}

        &middot;

        <a rel="noopener" target="_blank" href="https://github.com/Rudloff/alltube">
            {t}Get the code{/t}
        </a>

        &middot;

        {$youtubedl="<a href='http://ytdl-org.github.io/youtube-dl/'>
            youtube-dl
        </a>"}
        {t params=['@youtubedl'=>$youtubedl]}Based on @youtubedl{/t}

        &middot;

        <a rel="noopener" target="_blank" title="{t}Donate using Liberapay{/t}"
           href="https://liberapay.com/Rudloff/donate">
            {t}Donate{/t}
        </a>
    </div>
</footer>
</div>
</body>
</html>
