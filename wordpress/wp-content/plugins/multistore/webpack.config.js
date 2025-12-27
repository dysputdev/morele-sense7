const scriptConfig = require('@wordpress/scripts/config/webpack.config');
const glob = require('glob');

function getPluginEntries() {
    const pluginEntries = {};
    // Szukaj wszystkich plików .js w src/plugins/
    const pluginFiles = glob.sync('./src/plugins/**/index.js');
    pluginFiles.forEach(file => {
        const name = file
            .replace('./src/', '')
            .replace('.js', '');
        pluginEntries[name] = file;
    });

    return pluginEntries;
}

module.exports = {
	...scriptConfig,
	entry: {
		...scriptConfig.entry(),
        'editor': './src/editor.js',
		// ...getPluginEntries(),
		// 'plugins': './src/plugins'
	},
};