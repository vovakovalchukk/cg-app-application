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
                        cwd: 'public/channelgrabber/',
                        src: ['**/js/**/*.js', '!**/*.jsx'],
                        dest: 'public/cg-built/'
                    },
                ],
            }
        },
        watch: {
            babel: {
                files: 'public/channelgrabber/**/*.jsx',
                tasks: ['babel']
            },
            copy: {
                files: 'public/channelgrabber/**/*.js',
                tasks: ['copy']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['watch']);
};
