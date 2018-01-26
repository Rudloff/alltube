/*jslint node: true */
module.exports = function (grunt) {
    'use strict';
    grunt.initConfig(
        {
            githash: {
                main: {
                    options: {}
                }
            },
            cssmin: {
                combine: {
                    files: {
                        'dist/main.css': ['css/*.css']
                    }
                }
            },
            watch: {
                styles: {
                    files: ['css/*.css'],
                    tasks: ['cssmin']
                }
            },
            phpcs: {
                options: {
                    standard: 'PSR2',
                    bin: 'vendor/bin/phpcs'
                },
                php: {
                    src: ['*.php', 'classes/*.php', 'controllers/*.php']
                },
                tests: {
                    src: ['tests/*.php']
                }
            },
            jslint: {
                js: {
                    src: ['js/*.js']
                },
                Gruntfile: {
                    src: ['Gruntfile.js']
                }
            },
            phpunit: {
                options: {
                    bin: 'vendor/bin/phpunit',
                    stopOnError: true,
                    stopOnFailure: true,
                    followOutput: true
                },
                classes: {
                    dir: 'tests/'
                }
            },
            compress: {
                release: {
                    options: {
                        archive: 'alltube-<%= githash.main.tag %>.zip'
                    },
                    src: ['*.php', 'config/*', '!config/config.yml', 'dist/**', '.htaccess', 'img/**', 'LICENSE', 'README.md', 'robots.txt', 'resources/sitemap.xml', 'resources/manifest.json', 'templates/**', 'templates_c/', 'vendor/**', 'classes/**', 'controllers/**', 'bower_components/**', 'i18n/**', '!vendor/ffmpeg/**', '!vendor/bin/ffmpeg', '!vendor/phpunit/**', '!vendor/squizlabs/**', '!vendor/rinvex/country/resources/geodata/*.json', '!vendor/rinvex/country/resources/flags/*.svg', 'node_modules/open-sans-fontface/fonts/**']
                }
            },
            phpdocumentor: {
                doc: {
                    options: {
                        directory: 'classes/,controllers/,tests/'
                    }
                }
            },
            jsonlint: {
                manifests: {
                    src: ['*.json', 'resources/*.json'],
                    options: {
                        format: true
                    }
                }
            },
            fixpack: {
                package: {
                    src: 'package.json'
                }
            },
            potomo: {
                dist: {
                    options: {
                        poDel: false
                    },
                    files: {
                        'i18n/fr_FR/LC_MESSAGES/Alltube.mo': 'i18n/fr_FR/LC_MESSAGES/Alltube.po',
                        'i18n/zh_CN/LC_MESSAGES/Alltube.mo': 'i18n/zh_CN/LC_MESSAGES/Alltube.po',
                        'i18n/es_ES/LC_MESSAGES/Alltube.mo': 'i18n/es_ES/LC_MESSAGES/Alltube.po',
                        'i18n/pt_BR/LC_MESSAGES/Alltube.mo': 'i18n/pt_BR/LC_MESSAGES/Alltube.po'
                    }
                }
            },
            csslint: {
                options: {
                    'box-sizing': false,
                    'bulletproof-font-face': false
                },
                css: {
                    src: 'css/*'
                }
            },
            markdownlint: {
                doc: {
                    src: ['README.md', 'CONTRIBUTING.md', 'resources/*.md']
                }
          }
        }
    );

    grunt.loadNpmTasks('grunt-githash');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-jslint');
    grunt.loadNpmTasks('grunt-phpdocumentor');
    grunt.loadNpmTasks('grunt-jsonlint');
    grunt.loadNpmTasks('grunt-fixpack');
    grunt.loadNpmTasks('grunt-potomo');
    grunt.loadNpmTasks('grunt-contrib-csslint');
    grunt.loadNpmTasks('grunt-markdownlint');

    grunt.registerTask('default', ['cssmin', 'potomo']);
    grunt.registerTask('lint', ['csslint', 'fixpack', 'jsonlint', 'markdownlint', 'phpcs']);
    grunt.registerTask('test', ['phpunit']);
    grunt.registerTask('doc', ['phpdocumentor']);
    grunt.registerTask('release', ['default', 'githash', 'compress']);
};
