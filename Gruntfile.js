module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
                
        pkg: grunt.file.readJSON( 'package.json' ),
        paths : {
            // Base destination dir
            base: './dist/releases/mashshare/tags/<%= pkg.version %>',
            basetrunk: './dist/releases/mashshare/trunk/',
            basetags: './dist/releases/mashshare/tags/',
            basezip: './dist/releases/mashshare/',
            baseassets: './dist/releases/mashshare/assets',
            tmp: './dist/releases/mashshare/tmp'
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
                    {'<%= paths.base %>/assets/js/mashsb-admin.min.js' : 'assets/js/mashsb-admin.js'},
                    {'<%= paths.base %>/assets/js/mashsb.min.js' : 'assets/js/mashsb.js'}
                ]
            }
        },
        // Copy to build folder
        copy: {
            build: {             
                files: [
                    {expand: true, src: ['**',
                            '!node_modules/**',
                            '!docs/**',
                            '!Gruntfile.js',
                            '!package.json',
                            '!nbproject/**',
                            '!grunt/**',
                            '!tests/**',
                            '!bin/**',
                            '!.travis.yml',
                            '!phpunit.xml.dist',
                            '!package-lock.json',
                            '!dist/**',
                            '!mashsharer.zip',
                            '!composer.json'],
                     dest: '<%= paths.base %>'},
                 
                    {expand: true, src: ['**',
                            '!node_modules/**',
                            '!docs/**',
                            '!Gruntfile.js',
                            '!package.json',
                            '!nbproject/**',
                            '!grunt/**',
                            '!tests/**',
                            '!bin/**',
                            '!.travis.yml',
                            '!phpunit.xml.dist',
                            '!package-lock.json',
                            '!dist/**',
                            '!mashsharer.zip',
                            '!composer.json'],
                    dest: '<%= paths.basetrunk %>'}
                ]                
            }
        },
        
        'string-replace': {
                build: {
                    files: {
                        '<%= paths.basetrunk %>/mashshare.php' : 'mashshare.php',
                        '<%= paths.base %>/mashshare.php' : 'mashshare.php',
                        '<%= paths.base %>/readme.txt': 'readme.txt',
                        '<%= paths.basetrunk %>readme.txt': 'readme.txt'
                    },
                    options: {
                        replacements: [{
                                pattern: /define\('MASHSB_DEBUG', true\);/g,
                                replacement: 'define(\'MASHSB_DEBUG\', false);'
                            },{
                                pattern: /{{ version }}/g,
                                replacement: '<%= pkg.version %>'
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
                    {src: ['<%= paths.basetrunk %>']}
                ]
               
            }
        },
        // Minify CSS files into NAME-OF-FILE.min.css
        cssmin: {
            build: { 
                files:[
                    {'<%= paths.base %>/assets/css/mashsb-admin.min.css' : 'assets/css/mashsb-admin.css'},
                    {'<%= paths.base %>/assets/css/mashsb.min.css' : 'assets/css/mashsb.min.css'},
                    {'<%= paths.base %>/assets/css/mashsb-amp.min.css' : 'assets/css/mashsb-amp.min.css'}
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
                src: ['**/*'],
                expand: true
            }
        },
        
        wp_deploy: {
            deploy: { 
                options: {
                    plugin_main_file: 'mashshare.php',
                    plugin_slug: 'mashsharer',
                    svn_user: 'renehermi',  
                    build_dir: '<%= paths.basetrunk %>', //relative path to your build directory
                    //assets_dir: '<%= paths.basezip %>', //relative path to your assets directory (optional).
                    tmp_dir: '<%= paths.tmp %>', //Location where your SVN repository is checked out to.
                    version: '<%= pkg.version %>'
                }
            }
        }

    });

    // Load all grunt plugins here
    require('load-grunt-tasks')(grunt);
    
    // Display task timing
    require('time-grunt')(grunt);

    // Build task
    //grunt.registerTask( 'build', [ 'compress:build' ]);
    grunt.registerTask( 'build', [ 'clean:build', 'copy:build', 'uglify:build', 'cssmin:build', 'string-replace:build', 'compress:build' ]);
    grunt.registerTask( 'deploy', [ 'wp_deploy:deploy' ] );
};