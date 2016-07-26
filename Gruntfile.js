module.exports = function(grunt) {

    grunt.initConfig({
        babel: {
            options: {
                plugins: ['transform-react-jsx'],
                presets: ['es2015', 'react']
            },
            jsx: {
                files: [{
                    expand: true,
                    cwd: 'public/channelgrabber/**/jsx/',
                    src: ['*.jsx'],
                    dest: 'public/cg-built/**/js/',
                    ext: '.js'
                }]
            }
        }
    });

    grunt.registerTask('default', ['babel']);
};
