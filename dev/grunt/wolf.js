module.exports = function(grunt) {

	grunt.registerTask( 'wolf', function() {
		grunt.task.run( [
			'ftpush:wolf',
			'notify:wolf'
		] );
	} );
};