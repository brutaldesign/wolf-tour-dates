module.exports = function(grunt) {

	grunt.registerTask( 'stage', function() {
		grunt.task.run( [
			'ftpush:stage',
			'notify:stage'
		] );
	} );
};