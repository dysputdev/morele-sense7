const path = require('path');
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

/**
 * Auto entry dla JS
 * src/js/foo.js → assets/js/foo.js
 */
const jsEntries = fs
	.readdirSync('./src/')
	.filter(file => file.endsWith('.js'))
	.reduce((entries, file) => {
		const name = path.parse(file).name;
		entries[name] = `./src/${file}`;
		return entries;
	}, {});

module.exports = {
	mode: 'production',

	entry: {
		...jsEntries,
	},

	output: {
		path: path.resolve(__dirname),
		filename: 'assets/js/[name].js',
	},

	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: 'babel-loader',
			},
			{
				test: /\.scss$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader',
				],
			},
		],
	},

	plugins: [
		new MiniCssExtractPlugin({
			filename: 'assets/css/[name].css'
		}),
	],

	devtool: 'source-map',
};
