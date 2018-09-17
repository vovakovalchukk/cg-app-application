module.exports = function (grunt) {
    printMessage('Started grunt dynamic');
    var packageConfig = grunt.file.readJSON('package.json');
    generateSymlinkTasks(packageConfig);

    var modulesNames = grunt.file.expand('public/channelgrabber/*').map(removePath).filter(removeNonCss);
    printMessage('Found modules: '+modulesNames.join(', '));

    generateWatchSubTasks();
    generateShellSubTasks();
    generateInstallCommand();
    printMessage('Finished grunt dynamic');

    function generateWatchSubTasks() {
        var watchTasks = grunt.config('watch');
        modulesNames.forEach(function(module) {
            watchTasks[replaceAll(module, '-', '')] = {
                files: ["public/channelgrabber/" + module + "/**/*.scss"],
                tasks: ["shell:compile" + replaceAll(module, '-', '') + "Css", "shell:copy" + replaceAll(module, '-', '') + "Css"]
            };
        });
        grunt.config.set('watch', watchTasks);
    }

    function generateShellSubTasks() {
        var shellTasks = grunt.config('shell');
        modulesNames.forEach(function(module) {
            shellTasks["cleanSrc" + replaceAll(module, '-', '') + "Css"] = {
                command: "rm -rf public/channelgrabber/"+ module +"/css/*"
            };
            shellTasks["compile" + replaceAll(module, '-', '') + "Css"] = {
                command: "compass compile public/channelgrabber/" + module
            };
            shellTasks["copy" + replaceAll(module, '-', '') + "Css"] = {
                command: "cp -r public/channelgrabber/"+ module +"/css/* public/cg-built/"+ module +"/css/ 2>/dev/null || :"
            };
        });
        console.log(shellTasks);
        grunt.config.set('shell', shellTasks);
    }

    function generateInstallCommand() {
        var tasks = [];
        modulesNames.forEach(function(module){
            tasks.push("shell:cleanSrc" + replaceAll(module, '-', '') + "Css");
            tasks.push("shell:compile" + replaceAll(module,'-',  '') + "Css");
            tasks.push("shell:copy" + replaceAll(module, '-', '') + "Css");
        });
        grunt.registerTask('compileCss-gen', tasks);
    }

    function generateSymlinkTasks(packageConfig) {
        var shellTasks = grunt.config('shell');
        var tasks = [];

        for (var dependency in packageConfig['dependencies']){
            if (!packageConfig['dependencies'].hasOwnProperty(dependency)) continue;
            printMessage('Found dependency: '+dependency);

            shellTasks["symLink" + replaceAll(dependency, '-', '')] = {
                command: getSymlinkCommand(dependency)
            };
            tasks.push("shell:symLink" + replaceAll(dependency, '-', ''));
        }

        grunt.config.set('shell', shellTasks);
        grunt.registerTask('symLinkVendorJs-gen', tasks);
    }

    function removePath(element) {
        return element.split('/').pop();
    }
    function removeNonCss(element) {
        return (grunt.file.exists('public/channelgrabber/'+element+'/config.rb'));
    }
    function replaceAll(string, search, replace) {
        return string.replace(new RegExp(search, 'g'), replace);
    }
    function getSymlinkCommand(dependency) {
        return 'if [ -d public/channelgrabber/vendor/' + dependency + ' ] ; ' +
        'then echo "Symlink already exists for ' + dependency + '" ; ' +
        'else ln -s ' + __dirname + '/node_modules/' + dependency + ' public/channelgrabber/vendor' +
        '; echo "Symlink created for ' + dependency + '";fi'
    }
    function printMessage(message) {
        console.log('grunt-dynamic.js - '+message);
    }
};