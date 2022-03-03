

/*jshint node: true */

'use strict';

module.exports = function(grunt) {
	
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		cfg: {
			releasesSrcPath: ''
		},
		less: {
			options: {
				optimization: 2,
				compress: false,
				yuicompress: false
			},
			Diga: {
				files: {
					"skins/Diga/styles.css": "skins/Diga/less/styles.less",
					"skins/Diga/styles-mobile.css": "skins/Diga/less/styles-mobile.less"
				}
			}
		},
		watch: {
			options: {
				nospawn: true
			},
			SkinsLess: {
				files: ['skins/**/less/**/*.less'],
				tasks: ['less']
			}
		}
	});

	// dependencies
	for (var key in grunt.file.readJSON('package.json').devDependencies) {
		if (key.indexOf('grunt-') === 0) {
			grunt.loadNpmTasks(key);
		}
	}

	grunt.registerTask('default', ['less']);
	grunt.registerTask('w', ['default', 'watch']);
	grunt.registerTask('watch-css-only', ['w']);
};
