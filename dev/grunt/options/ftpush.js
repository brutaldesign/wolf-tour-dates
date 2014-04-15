module.exports = {
	
	// push to stage server
	stage: {
		auth: {
			host: '<%= app.remote %>',
			port: 21,
			authKey: 'stageKey'
		},
		src: '../pack/<%= app.slug %>',
		dest: '/<%= app.stageRemotePath %>/<%= app.slug %>',
		exclusions: ['../pack/<%= app.slug %>/dist'],
		simple: false,
		useList: false
	},
	// push to production
	prod: {
		auth: {
			host: '<%= app.remote %>',
			port: 21,
			authKey: 'prodKey'
		},
		src: '../pack/<%= app.slug %>',
		dest: '/<%= app.prodRemotePath %>/<%= app.slug %>',
		exclusions: ['../pack/<%= app.slug %>/dist'],
		simple: false,
		useList: false
	},
	// push to demo
	wolf: {
		auth: {
			host: '<%= app.remote %>',
			port: 21,
			authKey: 'prodKey'
		},
		src: '../pack/<%= app.slug %>',
		dest: '/<%= app.wolfRemotePath %>/<%= app.slug %>',
		exclusions: ['../pack/<%= app.slug %>/dist'],
		simple: false,
		useList: false
	},
	dist:{
		auth: {
			host: '<%= app.remote %>',
			port: 21,
			authKey: 'prodKey'
		},
		src: '../pack/dist',
		dest: '/<%= app.distRemotePath %>/<%= app.slug %>',
		simple: false,
		useList: false
	}
};