module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);
    grunt.initConfig({
        babel: {
            options: {
                presets: ['react']
            },
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/channelgrabber/',
                        src: ['**/jsx/**/*.jsx'],
                        dest: 'public/cg-built/',
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
            }
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
                    proxy: {
                        target: "https://app.dev.orderhub.io",
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
            }
        }
    });
    require('./grunt-dynamic.js')(grunt);

    grunt.registerTask('default', ['browserSync', 'watch']);

    grunt.registerTask('copyVanillaJs', ['copy:vanillaJsToGeneratedJs']);
    grunt.registerTask('compileJsx', ['babel']);

    grunt.registerTask('install:css', ['compileCss-gen']);
    grunt.registerTask('install:js', ['symLinkVendorJs-gen', 'compileJsx', 'copyVanillaJs', 'requirejs:compile']);

    grunt.registerTask('install', ['install:css', 'install:js']);
};
