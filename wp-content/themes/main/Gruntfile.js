'use strict';
const sass = require('node-sass');
module.exports = function (grunt) {
    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            options: {
                implementation: sass,
                sourceMap: false
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: 'assets/scss',
                    src: '*.scss',
                    dest: './',
                    ext: '.css'
                }]
            }
        },
        postcss: {
            dev: {
                options: {
                    map: true,
                    processors: [
                        require('autoprefixer')()
                    ]
                },
                files: [{
                    src: [
                        '*.css',
                        '!*.min.css'
                    ]
                }]
            },
            minify: {
                options: {
                    processors: [
                        require('autoprefixer')(),
                        require('cssnano')({
                            preset: ['default',{
                                discardComments: {
                                    removeAll: true
                                }
                            }]
                        })
                    ]
                },
                files: [{
                    expand: true,
                    src: [
                        '*.css',
                        '!*.min.css'
                    ],
                    ext: '.min.css'
                }]
            }
        },
        uglify: {
            options: {
                mangle: true
            },
            my_target: {
                files: [{
                    expand: true,
                    cwd: 'assets/js/scripts/',
                    src: ['*.js', '!*min.js'],
                    dest: 'assets/js/scripts/',
                    ext: '.min.js'
                }]
            }
        },
        watch: {
            styles: {
                files: [
                    'assets/scss/**/*.scss'
                ],
                tasks: ['styles']
            },
            scripts: {
                files: ['assets/js/scripts/**/*.js'],
                tasks: ['uglify']
            }
        },
        checktextdomain: {
            options: {
                text_domain: 'thecore',
                correct_domain: true,
                keywords: [
                    '__:1,2d',
                    '_e:1,2d',
                    '_x:1,2c,3d',
                    'esc_html__:1,2d',
                    'esc_html_e:1,2d',
                    'esc_html_x:1,2c,3d',
                    'esc_attr__:1,2d',
                    'esc_attr_e:1,2d',
                    'esc_attr_x:1,2c,3d',
                    '_ex:1,2c,3d',
                    '_n:1,2,4d',
                    '_nx:1,2,4c,5d',
                    '_n_noop:1,2,3d',
                    '_nx_noop:1,2,3c,4d'
                ]
            },
            files: {
                src: [
                    '**/*.php',
                    '!docs/**',
                    '!bin/**',
                    '!node_modules/**',
                    '!build/**',
                    '!tests/**',
                    '!.github/**',
                    '!vendor/**',
                    '!*~'
                ],
                expand: true
            },
        },
    });
    grunt.registerTask('i18n', [
        'checktextdomain',
    ]);
    grunt.registerTask('styles', [
        'sass',
        'postcss'
    ]);
    // Default task(s).
    grunt.registerTask('default', [
        // 'i18n',
        'styles',
    ]);

    grunt.loadNpmTasks('grunt-contrib-uglify-es');
};