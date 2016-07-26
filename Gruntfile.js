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
        }
    });

    grunt.registerTask('default', ['babel']);
};
