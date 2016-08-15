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
                        src: ['**/js/**/*.jsx'],
                        dest: 'public/cg-built/',
                        ext: '.js'
                    }
                ]
            }
        },
        copy: {
            main: {
                files: [
                    {
                        expand: true,
                        src: [
                            'public/channelgrabber/**/*.js',
                            '!public/channelgrabber/**/*.jsx'
                        ],
                        dest: 'public/cg-built/'
                    }
                ]
            },
            vendor: {
                files: [
                    {
                        expand: true,
                        cwd: 'vendor/channelgrabber/',
                        src: '**/*.js',
                        dest: 'public/cg-built/'
                    }
                ]
            }
        },
        shell: {
            options: {
                stderr: false
            },
            triggerSync: {
                command: "rm .sync; touch .sync"
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

            copySettings: {
                command: "rm -rf public/cg-built/settings/css/* ; mkdir public/cg-built/settings/css; cp -r public/channelgrabber/settings/css/* public/cg-built/settings/css/"
            },
            copySetupWizard: {
                command: "rm -rf public/cg-built/setup-wizard/css/* ; mkdir public/cg-built/setup-wizard/css; cp -r public/channelgrabber/setup-wizard/css/* public/cg-built/setup-wizard/css/"
            },
            copyV4Ui: {
                command: "rm -rf public/cg-built/zf2-v4-ui/css/* ; mkdir public/cg-built/zf2-v4-ui/css; cp -r public/channelgrabber/zf2-v4-ui/css/* public/cg-built/zf2-v4-ui/css/"
            },
            copyRegisterModule: {
                command: "rm -rf public/cg-built/zf2-register/css/* ; mkdir public/cg-built/zf2-register/css; cp -r public/channelgrabber/zf2-register/css/* public/cg-built/zf2-register/css/"
            }
        },
        watch: {
            babel: {
                files: 'public/channelgrabber/**/*.jsx',
                tasks: ['babel']
            },
            copyApplicationJs: {
                files: 'public/channelgrabber/**/*.js',
                tasks: ['copy:main']
            },
            copyVendorJs: {
                files: 'vendor/channelgrabber/**/*.js',
                tasks: ['copy:vendor']
            },
            compileV4UiCss: {
                files: 'vendor/channelgrabber/zf2-v4-ui/**/*.scss',
                tasks: ['compileV4Ui']
            },
            compileRegisterModuleCss: {
                files: 'vendor/channelgrabber/zf2-register/**/*.scss',
                tasks: ['compileRegisterModule']
            },
            compileSettingsCss: {
                files: 'public/channelgrabber/settings/**/*.scss',
                tasks: ['compileSettings']
            },
            compileSetupWizardCss: {
                files: 'public/channelgrabber/setup-wizard/**/*.scss',
                tasks: ['compileSetupWizard']
            }
        }
    });

    grunt.registerTask('default', ['watch']);

    grunt.registerTask('compileV4Ui', ['shell:cleanV4Ui', 'shell:compileV4Ui', 'shell:copyV4Ui', 'shell:triggerSync']);
    grunt.registerTask('compileRegisterModule', ['shell:cleanRegisterModule', 'shell:compileRegisterModule', 'shell:copyRegisterModule', 'shell:triggerSync']);
    grunt.registerTask('compileSettings', ['shell:cleanSettings', 'shell:compileSettings', 'shell:copySettings', 'shell:triggerSync']);
    grunt.registerTask('compileSetupWizard', ['shell:cleanSetupWizard', 'shell:compileSetupWizard', 'shell:copySetupWizard', 'shell:triggerSync']);

    grunt.registerTask('compileVendorCss', ['compileV4Ui', 'compileRegisterModule']);
    grunt.registerTask('compileApplicationCss', ['compileSettings', 'compileSetupWizard']);

    grunt.registerTask('install', ['compileVendorCss', 'compileApplicationCss', 'babel']);
};
