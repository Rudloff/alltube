<footer class="small-font">
    <div class="footer_wrapper">
        {include file='snippets/dev.tpl' assign=dev}
        {t params=['@dev'=>$dev]}Code by @dev{/t}

        &middot;

        {include file='snippets/designer.tpl' assign=designer}
        {t params=['@designer' => $designer]}Design by @designer{/t}

        &middot;

        <a rel="noopener" target="_blank" href="https://github.com/Rudloff/alltube">
            {t}Get the code{/t}
        </a>

        &middot;

        {include file='snippets/youtubedl.tpl' assign=youtubedl}
        {t params=['@youtubedl'=>$youtubedl]}Based on @youtubedl{/t}
    </div>
</footer>
