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
            triggerSync: {
                command: "rm .sync; touch .sync"
            }
        },
        watch: {
            babel: {
                files: 'public/channelgrabber/**/jsx/**/*.jsx',
                tasks: ['babel', 'copy:appJsToCgBuilt']
            },
            copyVanillaJs: {
                files: 'public/channelgrabber/**/js-vanilla/**/*.js',
                tasks: ['copyVanillaJs', 'copy:appJsToCgBuilt']
            },
            copyLegacyJs: {
                files: 'public/channelgrabber/**/js/**/*.js',
                tasks: ['copy:appJsToCgBuilt']
            }
        }
    });
    require('./grunt-dynamic.js')(grunt);

    grunt.registerTask('default', ['watch']);

    grunt.registerTask('copyVanillaJs', ['copy:vanillaJsToGeneratedJs']);
    grunt.registerTask('compileJsx', ['babel']);

    grunt.registerTask('install:css', ['compileCss-gen']);
    grunt.registerTask('install:js', ['compileJsx', 'copyVanillaJs', 'requirejs']);

    grunt.registerTask('install', ['install:css', 'install:js']);
};
