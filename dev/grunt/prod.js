module.exports = function(grunt) {

	grunt.registerTask( 'prod', function() {
		grunt.task.run( [
			'ftpush:prod',
			'notify:prod'
		] );
	} );
};