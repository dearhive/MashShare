/* local path 
cd "m:/github/Mashshare/mashshare-github-master/"
 * 
 */
module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
                
        pkg: grunt.file.readJSON( 'package.json' ),
        paths : {
            // Base destination dir
            base : '../../../../plugin/svn/tags/<%= pkg.version %>',
            basetrunk : '../../../../plugin/svn/trunk/',
            basezip: '../../../../plugin/svn/' 
        },

        // Tasks here
        // Bump version numbers
        version: {
            css: {
                options: {
                    prefix: 'Version\\:\\s'
                },
                src: [ 'style.css' ]
            },
            php: {
                options: {
                        prefix: '\@version\\s+'
                },
                src: [ 'functions.php', '<%= pkg.name %>.php' ]
            }
        },
        // minify js
        uglify: {
            build: { 
                files:[
                    {'assets/js/mashsb-admin.min.js' : 'assets/js/mashsb-admin.js'},
                    {'assets/js/mashsb.min.js' : 'assets/js/mashsb.js'},
                ]
            }
        },
        // Copy to build folder
        copy: {
            build: {             
                files: [
                    {expand: true, src: ['**', '!node_modules/**', '!Gruntfile.js', '!package.json', '!nbproject/**', '!grunt/**'],                
                     dest: '<%= paths.base %>'},
                 
                    {expand: true, src: ['**', '!node_modules/**', '!Gruntfile.js', '!package.json', '!nbproject/**', '!grunt/**'],
                    dest: '<%= paths.basetrunk %>'},
                ]                
            },
        },

        // Clean the build folder
        clean: {
            options: { 
                force: true 
            },
            build: {
                files:[
                    {src: ['<%= paths.base %>']},
                    {src: ['<%= paths.basetrunk %>']},
                ]
               
            }
        },
        // Minify CSS files into NAME-OF-FILE.min.css
        cssmin: {
            build: { 
                files:[
                    {'assets/css/mashsb-admin.min.css' : 'assets/css/mashsb-admin.css'},
                    {'templates/mashsb.min.css' : 'templates/mashsb.min.css'},
                ]
            }
        },
        // Compress the build folder into an upload-ready zip file
        compress: {
            build: {
                options: {
                    archive: '<%= paths.basezip %>/<%= pkg.name %>.zip'
                },
                cwd: '<%= paths.base %>',
                src: ['**/*']
                //dest: '../../',
                //expand: true
            }
        }


    });

    // Load all grunt plugins here
    // [...]
    //require('load-grunt-config')(grunt);
    require('load-grunt-tasks')(grunt);
    
    // Display task timing
    require('time-grunt')(grunt);

    // Build task
    //grunt.registerTask( 'build', [ 'compress:build' ]);
    grunt.registerTask( 'build', [ 'clean:build', 'uglify:build', 'cssmin:build', 'copy:build', 'compress:build' ]);
};