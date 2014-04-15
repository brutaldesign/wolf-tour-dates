module.exports = {

	build: {
		files: [
			{ cwd: '../', src: [ '**/*' ], dest: '../pack/<%= app.slug %>/' }
		],
		options: {
			ignore: '<%= app.ignoreFiles %>'
		}
	},
	
};