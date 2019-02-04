const webpackConfig = require('./webpack.config.js');
const chalk = require('chalk');

module.exports = function(grunt) {
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        babel: {
            es6: {
                options: {
                    presets: ['es2015']
                },
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/',
                        src: ['**/es6/**/*.es6'],
                        dest: 'public/channelgrabber/',
                        ext: '.js',
                        rename: function(dest, src) {
                            return dest + src.replace('es6', 'js');
                        }
                    }
                ]
            },
            react: {
                options: {
                    presets: ['react']
                },
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/',
                        src: ['**/jsx/**/*.jsx'],
                        dest: 'public/channelgrabber/',
                        ext: '.js',
                        rename: function(dest, src) {
                            return dest + src.replace('jsx', 'js');
                        }
                    }
                ]
            }
        },
        copy: {
            appJsToCgBuilt: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/',
                        src: ['**/js/**/*.js'],
                        dest: 'public/cg-built/'
                    }
                ]
            },
            vendorCssToCgBuilt: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/vendor/',
                        src: ['**/dist/**/*.css', '**/dist/**/*.map'],
                        dest: 'public/cg-built/vendor'
                    }
                ]
            },
            vendorJsToCgBuilt: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/vendor/',
                        src: [
                            '**/dist/**/*.min.js',
                            '**/umd/**/*.min.js',
                            'cg-*/dist/**/*.js'
                        ],
                        dest: 'public/cg-built/vendor'
                    }
                ]
            },
            vanillaJsToGeneratedJs: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/',
                        src: ['**/js-vanilla/**/*.js'],
                        dest: 'public/channelgrabber/',
                        rename: function(dest, src) {
                            return dest + src.replace('js-vanilla', 'js');
                        }
                    }
                ]
            },
            mustacheToCgBuilt: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/',
                        src: ['**/template/**/*.mustache'],
                        dest: 'public/cg-built/'
                    }
                ]
            }
        },
        browserSync: {
            dev: {
                bsFiles: {
                    src: [
                        'public/cg-built/**/*.js',
                        'public/cg-built/**/*.css'
                    ]
                },
                options: {
                    watchTask: true,
                    logLevel: "debug",
                    logConnections: true,
                    logFileChanges: true,
                    host: "app.dev.orderhub.io",
                    port: 443,
                    proxy: {
                        target: "https://192.168.33.53",
                        proxyReq: [
                            function(proxyReq) {
                                proxyReq.setHeader('Host', 'app.dev.orderhub.io');
                            }
                        ]
                    }
                }
            }
        },
        requirejs: {
            compile: {
                options: {
                    appDir: "public/channelgrabber",
                    baseUrl: "zf2-v4-ui/js/",
                    mainConfigFile: "public/channelgrabber/zf2-v4-ui/js/main.js",
                    dir: "public/cg-built",
                    paths: {
                        orders: "../../../public/channelgrabber/orders",
                        Filters: "../../../public/channelgrabber/filters/js"
                    },
                    modules: [{
                        name: "main"
                    }, {
                        name: "element/moreButton"
                    }, {
                        name: "popup/mustache"
                    }],
                    logLevel: 0
                }
            }
        },
        shell: {
            symlinkJsDeps: {
                command: "rm .sync; touch .sync"
            }
        },
        webpack: {
            options: {
                stats: !process.env.NODE_ENV || process.env.NODE_ENV === 'development'
            },
            prod: getWebpackConfig.bind(this, grunt.option('env'))
        },
        watch: {
            babelReact: {
                files: 'public/channelgrabber/**/jsx/**/*.jsx',
                tasks: ['newer:babel:react']
            },
            babelEs6: {
                files: 'public/channelgrabber/**/es6/**/*.es6',
                tasks: ['newer:babel:es6']
            },
            copyVendorCss: {
                files: 'public/channelgrabber/vendor/**/dist/**/*.css',
                tasks: ['newer:copy:vendorCssToCgBuilt']
            },
            copyVendorJs: {
                files: 'public/channelgrabber/vendor/**/dist/**/*.js',
                tasks: ['newer:copy:vendorJsToCgBuilt']
            },
            copyVanillaJs: {
                files: 'public/channelgrabber/**/js-vanilla/**/*.js',
                tasks: ['newer:copy:vanillaJsToGeneratedJs']
            },
            copyLegacyJs: {
                files: 'public/channelgrabber/**/js/**/*.js',
                tasks: ['newer:copy:appJsToCgBuilt']
            },
            copyMustache: {
                files: 'public/channelgrabber/**/template/**/*.mustache',
                tasks: ['newer:copy:mustacheToCgBuilt']
            }
        }
    });
    require('./grunt-dynamic.js')(grunt);

    grunt.loadNpmTasks('grunt-webpack');

    grunt.registerTask('default', ['watch']);
    grunt.registerTask('syncWatch', ['browserSync', 'watch']);

    grunt.registerTask('copyVanillaJs', ['copy:vanillaJsToGeneratedJs']);

    grunt.registerTask('install:css', ['compileCss-gen']);
    grunt.registerTask('install:js', ['symLinkVendorJs-gen', 'copyVanillaJs', 'requirejs:compile']);
    grunt.registerTask('install:vendor', ['copy:vendorCssToCgBuilt', 'copy:vendorJsToCgBuilt']);

    grunt.registerTask('install', ['install:css', 'install:js', 'install:vendor', 'webpack']);
};

function getWebpackConfig(env) {
    if (env !== 'dev') {
        console.log(chalk.cyan('running webpack in production mode...'));
        console.log(chalk.italic.blue('To use webpack in development mode run `grunt webpack --env=dev`'));
        return webpackConfig
    }
    console.log(chalk.cyan('running webpack in development mode...'))
    return getDevAdjustedWebpackConfig(webpackConfig)
}

function getDevAdjustedWebpackConfig(webpackConfig){
    return Object.assign(webpackConfig, {
        mode: 'development',
        watch: true,
        watchOptions: {
            // delay applied so that webpack runs after cg-common's grunt tasks are executed
            aggregateTimeout: 1500,
            poll: true
        }
    })
}