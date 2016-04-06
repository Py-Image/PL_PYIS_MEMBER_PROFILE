'use strict';
module.exports = function (grunt) {

    // load all grunt tasks
    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),
        
        // Define Watch Tasks
        watch: {
            options: {
                livereload: true
            },
            sass: {
                files: ['build/sass/**/*.scss', '!build/sass/admin/**/*.scss'],
                tasks: ['sass:front', 'autoprefixer:front', 'notify:sass']
            },
            sass_admin: {
                files: ['build/sass/admin/**/*.scss'],
                tasks: ['sass:admin', 'autoprefixer:admin', 'notify:sass_admin']
            },
            js: {
                files: ['build/js/**/*.js', '!build/js/admin/**/*.js', '!build/js/customizer/**/*.js'],
                tasks: ['uglify:front', 'notify:js']
            },
            js_admin: {
                files: ['build/js/admin/**/*.js'],
                tasks: ['uglify:admin', 'notify:js_admin']
            },
            js_customizer: {
                files: ['build/js/customizer/**/*.js']  ,
                tasks: ['uglify:customizer', 'notify:js_customizer']
            },
            livereload: {
                files: ['**/*.html', '**/*.php', 'build/images/**/*.{png,jpg,jpeg,gif,webp,svg}', '!**/*ajax*.php']
            }
        },
        
        // SASS
        sass: {
            options: {
                sourceMap: true
            },
            front: {
                files: {
                    'style.css': 'build/sass/main.scss'
                }
            },
            admin: {
                files: {
                    'admin.css': 'build/sass/admin/admin.scss'
                }
            }
        },

        // Auto prefix our CSS with vendor prefixes
        autoprefixer: {
            options: {
                map: true
            },
            front: {
                src: 'style.css'
            },
            admin: {
                src: 'admin.css'
            }
        },

        // Uglify and concatenate
        uglify: {
            options: {
                sourceMap: true
            },
            front: {
                files: {
                    'script.js': [
                        // Vendor files

                        // Theme scripts
                        'build/js/**/*.js',
                        '!build/js/admin/**/*.js',
                        '!build/js/customizer/**/*.js',
                    ]
                }
            },
            admin: {
                files: {
                    'admin.js': [
                        'build/js/admin/**/*.js',
                    ]
                }
            },
            customizer: {
                files: {
                    'customizer-controls.js': [
                        'build/js/customizer/controls/**/*.js',
                    ],
                    'customizer-preview.js': [
                        'build/js/customizer/preview/**/*.js',
                    ]
                }
            }
        },

        notify: {
            js: {
                options: {
                    title: '<%= pkg.name %>',
                    message: 'JS Complete'
                }
            },
            js_admin: {
                options: {
                    title: '<%= pkg.name %>',
                    message: 'JS Admin Complete'
                }
            },
            js_customizer: {
                options: {
                    title: '<%= pkg.name %>',
                    message: 'JS Customizer Complete'
                }  
            },
            sass: {
                options: {
                    title: '<%= pkg.name %>',
                    message: 'SASS Complete'
                }
            },
            sass_admin: {
                options: {
                    title: '<%= pkg.name %>',
                    message: 'SASS Admin Complete'
                }
            }
        },

        po2mo: {
            files: {
                src: 'languages/*.pot',
                expand: true
                // On Windows, this command doesn't like to work.
                // Run "msgfmt -o languages/edia.mo languages/eida.pot" from CMD with PHP in your Environment %PATH% and it will work.
                // msgfmt is part of the "gettext" package, which must also be in your %PATH%. This can be obtained via a package manager like MinGW
            }
        },

        makepot: {
            target: {
                options: {
                    type: 'wp-plugin',
                    domainPath: '/languages',
                    potFileName: 'pyis-member-profile.pot',
                    mainFile: 'pyis-member-profile.php',
                    // Similar story with this one, but you simply need to run "grunt makepot" from CMD rather than something like MinGW's Terminal Emulator
                }
            }
        }

    });
    
};