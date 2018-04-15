var webpack = require('webpack')

var ExtractTextPlugin = require('extract-text-webpack-plugin')

module.exports = {
    entry: {
        "./js/app": "./resources/assets/js/app.js",
    },
    output: {
        path: __dirname + '/public/',
        filename: 'app.bundle.js'
    },
    module: {
        loaders: [
            { test: /\.js$/,        loader: 'babel-loader'},
            { test: /\.vue$/,       loader: 'vue-loader'},
            { test: /\.html$/,      loader: 'html-loader'},
            { test: /\.svg$/,       loader: 'file-loader?limit=65000&mimetype=image/svg+xml&name=resources/fonts/[name].[ext]' },
            { test: /\.woff$/,      loader: 'file-loader?limit=65000&mimetype=font-woff&name=resources/fonts/[name].[ext]' },
            { test: /\.woff2$/,     loader: 'file-loader?limit=65000&mimetype=font-woff2&name=resources/fonts/[name].[ext]' },
            { test: /\.[ot]tf$/,    loader: 'file-loader?limit=65000&mimetype=octet-stream&name=resources/fonts/[name].[ext]' },
            { test: /\.eot$/,       loader: 'file-loader?limit=65000&mimetype=vnd.ms-fontobject&name=resources/fonts/[name].[ext]' }
        ],

        rules: [
            { 
                test: /\.css$/,       
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: 'css-loader'
                })
            },
            { 
                test: /\.scss$/,      
                loader: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: 'css-loader!sass-loader'
                })
            }
        ]
    },

    watchOptions: {
      aggregateTimeout: 300,
      poll: 1000,
      ignored: /node_modules/
    },
    plugins: [
        new ExtractTextPlugin('app.bundle.css')
    ]
};