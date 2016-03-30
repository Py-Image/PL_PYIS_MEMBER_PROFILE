'use strict';
module.exports = function (grunt) {

    // load all grunt tasks
    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

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