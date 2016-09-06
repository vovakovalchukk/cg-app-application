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
            },

            compileProducts: {
                command: "compass compile public/channelgrabber/products"
            },
            compileOrders: {
                command: "compass compile public/channelgrabber/orders"
            },
            compileSettings: {
                command: "compass compile public/channelgrabber/settings"
            },
            compileSetupWizard: {
                command: "compass compile public/channelgrabber/setup-wizard"
            },
            compileV4Ui: {
                command: "compass compile vendor/channelgrabber/zf2-v4-ui"
            },
            compileRegisterModule: {
                command: "compass compile vendor/channelgrabber/zf2-register"
            },

            cleanProducts: {
                command: "rm -rf public/channelgrabber/products/css/*"
            },
            cleanOrders: {
                command: "rm -rf public/channelgrabber/orders/css/*"
            },
            cleanSettings: {
                command: "rm -rf public/channelgrabber/settings/css/*"
            },
            cleanSetupWizard: {
                command: "rm -rf public/channelgrabber/setup-wizard/css/*"
            },
            cleanV4Ui: {
                command: "rm -rf vendor/channelgrabber/zf2-v4-ui/css/*"
            },
            cleanRegisterModule: {
                command: "rm -rf vendor/channelgrabber/zf2-register/css/*"
            },

            copyProducts: {
                command: "rm -rf public/cg-built/products/css/* ; mkdir -p public/cg-built/products/css; cp -r public/channelgrabber/products/css/* public/cg-built/products/css/"
            },
            copyOrders: {
                command: "rm -rf public/cg-built/orders/css/* ; mkdir -p public/cg-built/orders/css; cp -r public/channelgrabber/orders/css/* public/cg-built/orders/css/"
            },
            copySettings: {
                command: "rm -rf public/cg-built/settings/css/* ; mkdir -p public/cg-built/settings/css; cp -r public/channelgrabber/settings/css/* public/cg-built/settings/css/"
            },
            copySetupWizard: {
                command: "rm -rf public/cg-built/setup-wizard/css/* ; mkdir -p public/cg-built/setup-wizard/css; cp -r public/channelgrabber/setup-wizard/css/* public/cg-built/setup-wizard/css/"
            },
            copyV4Ui: {
                command: "rm -rf public/cg-built/zf2-v4-ui/css/* ; mkdir -p public/cg-built/zf2-v4-ui/css; cp -r public/channelgrabber/zf2-v4-ui/css/* public/cg-built/zf2-v4-ui/css/"
            },
            copyRegisterModule: {
                command: "rm -rf public/cg-built/zf2-register/css/* ; mkdir -p public/cg-built/zf2-register/css; cp -r public/channelgrabber/zf2-register/css/* public/cg-built/zf2-register/css/"
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
            },
            compileV4UiCss: {
                files: 'vendor/channelgrabber/zf2-v4-ui/**/*.scss',
                tasks: ['compileV4UiCss']
            },
            compileRegisterModuleCss: {
                files: 'vendor/channelgrabber/zf2-register/**/*.scss',
                tasks: ['compileRegisterModuleCss']
            },
            compileProductsCss: {
                files: 'public/channelgrabber/products/**/*.scss',
                tasks: ['compileProductsCss']
            },
            compileOrdersCss: {
                files: 'public/channelgrabber/orders/**/*.scss',
                tasks: ['compileOrdersCss']
            },
            compileSettingsCss: {
                files: 'public/channelgrabber/settings/**/*.scss',
                tasks: ['compileSettingsCss']
            },
            compileSetupWizardCss: {
                files: 'public/channelgrabber/setup-wizard/**/*.scss',
                tasks: ['compileSetupWizardCss']
            }
        }
    });

    grunt.registerTask('default', ['watch']);

    grunt.registerTask('compileV4UiCss', ['shell:cleanV4Ui', 'shell:compileV4Ui', 'shell:copyV4Ui', 'shell:triggerSync']);
    grunt.registerTask('compileRegisterModuleCss', ['shell:cleanRegisterModule', 'shell:compileRegisterModule', 'shell:copyRegisterModule', 'shell:triggerSync']);
    grunt.registerTask('compileProductsCss', ['shell:cleanProducts', 'shell:compileProducts', 'shell:copyProducts', 'shell:triggerSync']);
    grunt.registerTask('compileOrdersCss', ['shell:cleanOrders', 'shell:compileOrders', 'shell:copyOrders', 'shell:triggerSync']);
    grunt.registerTask('compileSettingsCss', ['shell:cleanSettings', 'shell:compileSettings', 'shell:copySettings', 'shell:triggerSync']);
    grunt.registerTask('compileSetupWizardCss', ['shell:cleanSetupWizard', 'shell:compileSetupWizard', 'shell:copySetupWizard', 'shell:triggerSync']);

    grunt.registerTask('compileVendorCss', ['compileV4UiCss', 'compileRegisterModuleCss']);
    grunt.registerTask('compileApplicationCss', ['compileSettingsCss', 'compileSetupWizardCss', 'compileProductsCss', 'compileOrdersCss']);

    grunt.registerTask('copyVanillaJs', ['copy:vanillaJsToGeneratedJs']);
    grunt.registerTask('compileJsx', ['babel']);

    grunt.registerTask('install', ['compileVendorCss', 'compileApplicationCss', 'compileJsx', 'copyVanillaJs', 'requirejs']);
};
