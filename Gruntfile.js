/*jslint node: true */
module.exports = function (grunt) {
    'use strict';
    grunt.initConfig(
        {
            uglify: {
                combine: {
                    files: {
                        'dist/main.js': ['js/cast.js']
                    }
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
                scripts: {
                    files: ['js/*.js'],
                    tasks: ['uglify']
                },
                styles: {
                    files: ['css/*.css'],
                    tasks: ['cssmin']
                }
            },
            phpcs: {
                php: {
                    src: ['*.php', 'classes/*.php', 'controllers/*.php']
                },
                tests: {
                    src: ['tests/*.php']
                },
                js: {
                    src: ['js/*.js']
                },
                Gruntfile: {
                    src: ['Gruntfile.js']
                }
            },
            phpunit: {
                classes: {
                    dir: 'tests/'
                }
            },
            compress: {
                release: {
                    options: {
                        archive: 'alltube-release.zip'
                    },
                    src: ['*.php', '!config.yml', 'dist/**', 'fonts/**', '.htaccess', 'img/**', 'js/**', 'LICENSE', 'README.md', 'robots.txt', 'sitemap.xml', 'templates/**', 'templates_c/', 'vendor/**', 'classes/**', 'controllers/**', 'bower_components/**', '!vendor/ffmpeg/**', '!vendor/bin/ffmpeg']
                }
            }
        }
    );

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-compress');

    grunt.registerTask('default', ['uglify', 'cssmin']);
    grunt.registerTask('lint', ['phpcs']);
    grunt.registerTask('test', ['phpunit']);
    grunt.registerTask('release', ['default', 'compress']);
};
