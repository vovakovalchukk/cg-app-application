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
                        rename: function (dest, src) {
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
                        rename: function (dest, src) {
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
                        src: [ '**/js/**/*.js'],
                        dest: 'public/cg-built/'
                    }
                ]
            },
            vendorJsToCgBuilt: {
                files: [
                    {
                        expand: true,
                        cwd: 'vendor/channelgrabber/',
                        src: [ '**/js/**/*.js'],
                        dest: 'public/cg-built/'
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
                        rename: function (dest, src) {
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
                        src: [ '**/template/**/*.mustache'],
                        dest: 'public/cg-built/'
                    }
                ]
            },
        },
        browserSync: {
            dev: {
                bsFiles: {
                    src : [
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
                        Filters: "../../../public/channelgrabber/filters/js",
                    },
                    modules: [{
                        name: "main"
                    }, {
                        name: "element/moreButton",
                    }, {
                        name: "popup/mustache",
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
        watch: {
            babel: {
                files: 'public/channelgrabber/**/jsx/**/*.jsx',
                tasks: ['newer:babel']
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

    grunt.registerTask('default', ['watch']);
    grunt.registerTask('syncWatch', ['browserSync', 'watch']);

    grunt.registerTask('copyVanillaJs', ['copy:vanillaJsToGeneratedJs']);
    grunt.registerTask('compileJsx', ['babel:react']);

    grunt.registerTask('compileEs6', ['babel:es6']);

    grunt.registerTask('install:css', ['compileCss-gen']);
    grunt.registerTask('install:js', ['symLinkVendorJs-gen', 'compileJsx', 'compileEs6', 'copyVanillaJs', 'requirejs:compile']);

    grunt.registerTask('install', ['install:css', 'install:js']);
};
