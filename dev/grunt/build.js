module.exports = function(grunt) {

	grunt.registerTask( 'build', function() {
		grunt.task.run( [
			'compass',
			'cssmin',
			'jshint',
			'uglify',
			'clean:build',
			'copyto:build',
			'compress:build',
			'notify:build'
		] );
	} );
};