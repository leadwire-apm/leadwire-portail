// Generated on 2015-04-08 using
// generator-angular 0.11.1
'use strict';

// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// use this if you want to recursively match all subfolders:
// 'test/spec/**/*.js'

module.exports = function (grunt) {

    // Load grunt tasks automatically
    require('load-grunt-tasks')(grunt);

    // Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    // Configurable paths for the application
    var config = {
        app: require('./bower.json').appPath || 'app',
        name: require('./bower.json').name || 'app',
        dist: 'dist'
    };

    // Define the configuration for all the tasks
    grunt.initConfig({

        // Project settings
        config: config,

        // Watches files for changes and runs tasks based on the changed files
        watch: {
            js: {
                files: ['<%= config.app %>/scripts/{,*/}*.js'],
                tasks: ['newer:jshint:all'],
                options: {
                    livereload: '<%= connect.options.livereload %>'
                }
            },
            jsTest: {
                files: ['test/spec/{,*/}*.js'],
                tasks: ['newer:jshint:test', 'karma']
            },
            less: {
                files: ['<%= config.app %>/styles/**/*.less'],
                tasks: ['less']
            },
            styles: {
                files: ['<%= config.app %>/styles/{,*/}*.css'],
                tasks: ['newer:copy:styles']
            },
            gruntfile: {
                files: ['Gruntfile.js']
            },
            livereload: {
                options: {
                    livereload: '<%= connect.options.livereload %>'
                },
                files: [
                    '<%= config.app %>/{,*/}*.html',
                    '.tmp/styles/{,*/}*.css',
                    '<%= config.app %>/images/{,*/}*.{png,jpg,jpeg,gif,webp,svg}'
                ]
            }
        },

        // The actual grunt server settings
        connect: {
            options: {
                port: 9001,
                // Change this to '0.0.0.0' to access the server from outside.
                hostname: 'localhost',
                // remove next from params
                middleware: function(connect, options) {
                    return [
                        function(req, res, next) {
                            res.setHeader('Access-Control-Allow-Origin', '*');
                            res.setHeader('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
                            res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

                            // don't just call next() return it
                            return next();
                        },

                        // add other middlewares here
                        connect.static(require('path').resolve('.'))

                    ];
                },
                livereload: 35729
            },
            livereload: {
                options: {
                    open: true,
                    middleware: function (connect) {
                        return [
                            connect.static('.tmp'),
                            connect().use(
                                '/vendor',
                                connect.static('./bower_components')
                            ),
                            connect().use(
                                '/app/styles',
                                connect.static('./app/styles')
                            ),
                            connect.static(config.app)
                        ];
                    }
                }
            },
            test: {
                options: {
                    port: 9001,
                    middleware: function (connect) {
                        return [
                            connect.static('.tmp'),
                            connect.static('test'),
                            connect().use(
                                '/vendor',
                                connect.static('./bower_components')
                            ),
                            connect.static(config.app)
                        ];
                    }
                }
            },
            dist: {
                options: {
                    open: true,
                    base: '<%= config.dist %>'
                }
            }
        },

        // Make sure code styles are up to par and there are no obvious mistakes
        jshint: {
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish'),
                ignores: ['<%= config.app %>/scripts/extentions/**/*.js']
            },
            all: {
                src: [
                    'Gruntfile.js',
                    '<%= config.app %>/scripts/{,*/}*.js'
                ]
            },
            test: {
                options: {
                    jshintrc: 'test/.jshintrc'
                },
                src: ['test/spec/{,*/}*.js']
            }
        },

        // Empties folders to start fresh
        clean: {
            dist: {
                files: [{
                    dot: true,
                    src: [
                        '.tmp',
                        '<%= config.dist %>/{,*/}*',
                        '!<%= config.dist %>/.git{,*/}*'
                    ]
                }]
            },
            server: '.tmp'
        },

        less: {
            compileCore: {
                options: {
                    strictMath: true
                },
                src: '<%= config.app %>/styles/less/leadwire.less',
                dest: '.tmp/styles/<%= config.name %>.css'
            },
            compileSkin: {
                options: {
                    strictMath: true
                },
                src: '<%= config.app %>/styles/less/leadwire.skins.less',
                dest: '.tmp/styles/<%= config.name %>.skins.css'
            }
        },

        // Add vendor prefixed styles
        autoprefixer: {
            options: {
                browsers: ['last 1 version']
            },
            server: {
                options: {
                    map: true,
                },
                files: [{
                    expand: true,
                    cwd: '.tmp/styles/',
                    src: '{,*/}*.css',
                    dest: '.tmp/styles/'
                }]
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: '.tmp/styles/',
                    src: '{,*/}*.css',
                    dest: '.tmp/styles/'
                }]
            }
        },

        // Renames files for browser caching purposes
        filerev: {
            dist: {
                src: [
                    '<%= config.dist %>/scripts/{,*}*.js',
                    '<%= config.dist %>/styles/{,*/}*.css',
                    '<%= config.dist %>/images/{,*/}*.{png,jpg,jpeg,gif,webp,svg}',
                ]
            }
        },

        // Reads HTML for usemin blocks to enable smart builds that automatically
        // concat, minify and revision files. Creates configurations in memory so
        // additional tasks can operate on them
        useminPrepare: {
            html: '<%= config.app %>/index.html',
            options: {
                dest: '<%= config.dist %>',
                flow: {
                    html: {
                        steps: {
                            js: ['concat', 'uglifyjs'],
                            css: ['cssmin']
                        },
                        post: {}
                    }
                }
            }
        },

        // Performs rewrites based on filerev and the useminPrepare configuration
        usemin: {
            html: ['<%= config.dist %>/**/*.html'],
            css: ['<%= config.dist %>/styles/{,*/}*.css'],
            js: ['<%= config.dist %>/scripts/*.js'],
            options: {
                assetsDirs: [
                    '<%= config.dist %>',
                    '<%= config.dist %>/images',
                    '<%= config.dist %>/styles'
                ],
                patterns: {
                    js: [
                        [/(images\/.*?\.(?:png|jpg|jepg|gif|webp|svg))/gm, 'Update the JS to reference our revved images'],
                        [/(styles\/.*?\.css)/gm, 'Update the JS to reference our revved styles']
                    ]
                }
            }
        },

        // The following *-min tasks will produce minified files in the dist folder
        // By default, your `index.html`'s <!-- Usemin block --> will take care of
        // minification. These next options are pre-configured if you do not wish
        // to use the Usemin blocks.
        // cssmin: {
        //   dist: {
        //     files: {
        //       '<%= config.dist %>/styles/main.css': [
        //         '.tmp/styles/{,*/}*.css'
        //       ]
        //     }
        //   }
        // },
        // uglify: {
        //   dist: {
        //     files: {
        //       '<%= config.dist %>/scripts/scripts.js': [
        //         '<%= config.dist %>/scripts/scripts.js'
        //       ]
        //     }
        //   }
        // },
        // concat: {
        //   dist: {}
        // },

        // imagemin: {
        // dist: {
        // files: [{
        // expand: false,
        // cwd: '<%= config.app %>/images',
        // src: '{,*/}*.jpg',
        // dest: '<%= config.dist %>/images'
        // }]
        // }
        // },

        svgmin: {
            dist: {
                files: [{
                    expand: true,
                    cwd: '<%= config.app %>/images',
                    src: '{,*/}*.svg',
                    dest: '<%= config.dist %>/images'
                }]
            }
        },

        htmlmin: {
            dist: {
                options: {
                    collapseWhitespace: true,
                    conservativeCollapse: true,
                    collapseBooleanAttributes: true,
                    removeCommentsFromCDATA: true,
                    removeOptionalTags: true
                },
                files: [{
                    expand: true,
                    cwd: '<%= config.dist %>',
                    src: ['*.html', 'views/{,*/}*.html'],
                    dest: '<%= config.dist %>'
                }]
            }
        },

        // ng-annotate tries to make the code safe for minification automatically
        // by using the Angular long form for dependency injection.
        ngAnnotate: {
            dist: {
                files: [{
                    expand: true,
                    cwd: '.tmp/concat/scripts',
                    src: '*.js',
                    dest: '.tmp/concat/scripts'
                }]
            }
        },

        // Replace Google CDN references
        cdnify: {
            dist: {
                html: ['<%= config.dist %>/*.html']
            }
        },

        // Copies remaining files to places other tasks can use
        copy: {
            dist: {
                files: [{
                    expand: true,
                    dot: true,
                    cwd: '<%= config.app %>',
                    dest: '<%= config.dist %>',
                    src: [
                        '*.{ico,png,txt}',
                        '.htaccess',
                        '*.html',
                        'data/{,*/}*.*',
                        'fonts/{,*/}*.*',
                        'views/{,*/}*.html',
                        'images/{,*/}*.*',
                        'styles/{,*}*.css',
                        'scripts/*/*.*'
                    ]
                }, {
                    expand: true,
                    cwd: 'bower_components/bootstrap/dist',
                    src: 'fonts/*',
                    dest: '<%= config.dist %>'
                }, {
                    expand: true,
                    cwd: 'bower_components',
                    dest: '<%= config.dist %>/vendor',
                    src: ['**']
                }]
            },
            styles: {
                expand: true,
                cwd: '<%= config.app %>/styles',
                dest: '.tmp/styles/',
                src: '{,*/}*.css'
            }
        },

        // Run some tasks in parallel to speed up the build process
        concurrent: {
            server: [
                'copy:styles'
            ],
            test: [
                'copy:styles'
            ],
            dist: [
                'copy:styles',
                // 'imagemin',
                'svgmin'
            ]
        },
        shell: {
            clean_app: {
                command: 'rm -rf ../app'
            },
            copy_dist: {
                command: 'mv dist ../app'
            },
            copy_images: {
                command: 'cp -r app/images ../app/'
            },
            copy_index: {
                command: 'mv ../app/index.html ../../views/Default/index.html.twig'
            },
            replace_index_js: {
                command: 'replace "scripts/" "bundles/ui/app/scripts/" -- ../../views/Default/index.html.twig'
            },
            replace_index_css: {
                command: 'replace "styles/" "bundles/ui/app/styles/" -- ../../views/Default/index.html.twig'
            },
            copy_vendor: {
                command: 'cp -r app/vendor/ ../app/'
            }
        },

        // Test settings
        karma: {
            unit: {
                configFile: 'test/karma.conf.js',
                singleRun: true
            }
        }
    });

    grunt.registerTask('less-compile', ['less:compileCore', 'less:compileSkin']);

    grunt.registerTask('serve', 'Compile then start a connect web server', function (target) {
        if (target === 'dist') {
            return grunt.task.run(['build', 'connect:dist:keepalive']);
        }

        grunt.task.run([
            'clean:server',
            'less-compile',
            'concurrent:server',
            'connect:livereload',
            'watch'
        ]);
    });

    grunt.registerTask('server', 'DEPRECATED TASK. Use the "serve" task instead', function (target) {
        grunt.log.warn('The `server` task has been deprecated. Use `grunt serve` to start a server.');
        grunt.task.run(['serve:' + target]);
    });

    grunt.registerTask('test', [
        'clean:server',
        'concurrent:test',
        'connect:test',
        'karma'
    ]);

    grunt.registerTask('build', [
        'clean:dist',
        'less-compile',
        'useminPrepare',
        'concurrent:dist',
        'concat',
        'ngAnnotate',
        'copy:dist',
        'cdnify',
        'cssmin',
        'uglify',
        'filerev',
        'usemin',
        'htmlmin',
        'shell'
    ]);

    grunt.registerTask('default', [
        'newer:jshint',
        'build'
    ]);
};
