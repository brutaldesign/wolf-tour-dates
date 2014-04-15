module.exports = function(grunt) {

	grunt.registerTask( 'dist', function() {
		grunt.task.run( [
			'ftpush:dist',
			'notify:dist'
		] );
	} );
};