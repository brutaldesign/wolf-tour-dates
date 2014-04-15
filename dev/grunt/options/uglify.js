module.exports = {
	
	options: {
		mangle: true,
		banner : '/*! <%= app.name %> v<%= app.version %> */\n'
	},

	dist: {
		files: {
			'../assets/admin/js/datepicker.min.js': [ '../assets/admin/js/datepicker.js']
		}
	}
	
};