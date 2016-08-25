/* local path 
cd "m:/github/Mashshare/mashshare-github-master/"
cd "/srv/www/wordpress-develop/src/wp-content/plugins/mashsharer"
 * 
 */
module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
                
        pkg: grunt.file.readJSON( 'package.json' ),
        paths : {
            // Base destination dir
            base: '../../../../plugin/svn/tags/<%= pkg.version %>',
            basetrunk: '../../../../plugin/svn/trunk/',
            basetags: '../../../../plugin/svn/tags/',
            basezip: '../../../../plugin/svn/',
            baseassets: '../../../../plugin/svn/assets',
            tmp: '../../../../plugin/svn/tmp'
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
                    {expand: true, src: ['**', '!node_modules/**', '!Gruntfile.js', '!package.json', '!nbproject/**', '!grunt/**','!tests/**', '!bin/**', '.travis.yml', '!phpunit.xml.dist'],                
                     dest: '<%= paths.base %>'},
                 
                    {expand: true, src: ['**', '!node_modules/**', '!Gruntfile.js', '!package.json', '!nbproject/**', '!grunt/**','!tests/**', '!bin/**', '.travis.yml', '!phpunit.xml.dist'],
                    dest: '<%= paths.basetrunk %>'},
                ]                
            },
        },
        
        'string-replace': {
                build: {
                    files: {
                        '<%= paths.basetrunk %>/mashshare.php' : 'mashshare.php',
                        '<%= paths.base %>/mashshare.php' : 'mashshare.php',
                    },
                    options: {
                        replacements: [{
                                pattern: /define\('MASHSB_DEBUG', true\);/g,
                                replacement: 'define(\'MASHSB_DEBUG\', false);'
                            }]
                    }
                }
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
                    {'assets/css/mashsb.min.css' : 'assets/css/mashsb.min.css'},
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
        },
        wp_deploy: {
            deploy: { 
                options: {
                    plugin_main_file: 'mashshare.php',
                    plugin_slug: 'mashsharer',
                    svn_user: 'renehermi',  
                    build_dir: '<%= paths.basezip %>', //relative path to your build directory
                    //assets_dir: '<%= paths.basezip %>', //relative path to your assets directory (optional).
                    tmp_dir: '<%= paths.tmp %>', //relative path tmp assets directory (optional).
                    version: '<%= pkg.version %>'
                },
            }
        },

    });

    // Load all grunt plugins here
    require('load-grunt-tasks')(grunt);
    
    // Display task timing
    require('time-grunt')(grunt);

    // Build task
    //grunt.registerTask( 'build', [ 'compress:build' ]);
    grunt.registerTask( 'build', [ 'clean:build', 'uglify:build', 'cssmin:build', 'copy:build', 'string-replace:build', 'compress:build' ]);
    grunt.registerTask( 'deploy', [ 'wp_deploy:deploy' ] )
};