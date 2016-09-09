module.exports = function (grunt) {
    console.log('grunt-dynamic.js - Started generating dynamic tasks');
    var modulesNames = grunt.file.expand('public/channelgrabber/*').map(removePath).filter(removeNonCss);
    console.log('grunt-dynamic.js - Found modules: '+modulesNames.join(', '));

    function generateWatchSubTasks() {
        var watchTasks = grunt.config('watch');
        modulesNames.forEach(function(module) {
            watchTasks[replaceAll(module, '-', '')] = {
                files: ["public/channelgrabber/" + module + "/**/*.scss"],
                tasks: ["shell:compile" + replaceAll(module, '-', '') + "Css"]
            };
        });
        grunt.config.set('watch', watchTasks);
    }

    function generateShellSubTasks() {
        var shellTasks = grunt.config('shell');
        modulesNames.forEach(function(module) {
            shellTasks["compile" + replaceAll(module, '-', '') + "Css"] = {
                command: "compass compile --force public/channelgrabber/" + module
            };
            shellTasks["cleanSrc" + replaceAll(module, '-', '') + "Css"] = {
                command: "rm -rf public/channelgrabber/"+ module +"/css/*"
            };
            shellTasks["cleanDest" + replaceAll(module, '-', '') + "Css"] = {
                command: "rm -rf public/cg-built/"+ module +"/css/*"
            };
            shellTasks["makeDir" + replaceAll(module, '-', '') + "Css"] = {
                command: "mkdir -p public/cg-built/"+ module +"/css"
            };
            shellTasks["copy" + replaceAll(module, '-', '') + "Css"] = {
                command: "cp -r public/channelgrabber/"+ module +"/css/* public/cg-built/"+ module +"/css/ 2>/dev/null || :"
            };
        });
        grunt.config.set('shell', shellTasks);
    }

    function generateInstallCommand() {
        var tasks = [];
        modulesNames.forEach(function(module){
             tasks.push("shell:compile" + replaceAll(module,'-',  '') + "Css");
             tasks.push("shell:cleanSrc" + replaceAll(module, '-', '') + "Css");
             tasks.push("shell:cleanDest" + replaceAll(module, '-', '') + "Css");
             tasks.push("shell:makeDir" + replaceAll(module, '-', '') + "Css");
             tasks.push("shell:copy" + replaceAll(module, '-', '') + "Css");
        });
        grunt.registerTask('compileCss-gen', tasks);
    }

    generateWatchSubTasks();
    generateShellSubTasks();
    generateInstallCommand();
    console.log('grunt-dynamic.js - Finished generating dynamic tasks');

    function removePath(element) {
        return element.split('/').pop();
    }
    function removeNonCss(element) {
        return (grunt.file.exists('public/channelgrabber/'+element+'/config.rb'));
    }
    function replaceAll(string, search, replace) {
        return string.replace(new RegExp(search, 'g'), replace);
    }
};