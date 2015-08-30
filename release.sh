rm alltube-release.zip
zip -r alltube-release.zip *.php dist/ fonts/ .htaccess img/ js/ LICENSE README.md robots.txt sitemap.xml  templates/ templates_c/ vendor/ youtube-dl -x config.php templates_c/*
