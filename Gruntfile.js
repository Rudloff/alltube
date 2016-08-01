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
                classes: {
                    dir: 'tests/'
                }
            },
            compress: {
                release: {
                    options: {
                        archive: 'alltube-<%= githash.main.tag %>.zip'
                    },
                    src: ['*.php', '!config.yml', 'dist/**', 'fonts/**', '.htaccess', 'img/**', 'js/**', 'LICENSE', 'README.md', 'robots.txt', 'sitemap.xml', 'templates/**', 'templates_c/', 'vendor/**', 'classes/**', 'controllers/**', 'bower_components/**', '!vendor/ffmpeg/**', '!vendor/bin/ffmpeg']
                }
            }
        }
    );

    grunt.loadNpmTasks('grunt-githash');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-jslint');

    grunt.registerTask('default', ['uglify', 'cssmin']);
    grunt.registerTask('lint', ['phpcs', 'jslint']);
    grunt.registerTask('test', ['phpunit']);
    grunt.registerTask('release', ['default', 'githash', 'compress']);
};
