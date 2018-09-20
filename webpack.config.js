const path = require('path');

module.exports = {
    mode: "development",
    entry: {
        Product: "./public/channelgrabber/products/jsx/Product/Product.jsx"
    },
    module: {
        rules:[
            {
                test: /\.jsx$/,
                loader: 'babel-loader',
                options: {
                    presets: ['es2015', 'react']
                }
            },
            { test: /thenBy/, use: 'exports-loader?firstBy' }
        ]
    },
    output: {
        path: path.resolve(__dirname, 'public', 'cg-built', 'dist'),
        filename: "[name].js",
        library: "[name]",
        libraryTarget: "amd"
    },
    resolve: {
        modules: [
            path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx'),
            path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'js-vanilla'),
            path.resolve(__dirname, 'public', 'channelgrabber', 'zf2-v4-ui', 'js'),
            "node_modules"
        ],
        extensions: ['.js', '.jsx'],
        alias: {
            // Can't have an alias for Product as it exists in both jsx and js-vanilla :(
            //Product: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'Product'),
            CategoryMapper: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'CategoryMapper'),
            Common: path.resolve(__dirname, 'node_modules', 'cg-common', 'src', 'jsx', 'Common'),
            // There's multiple copies of react-tether, specify one
            "react-tether": path.resolve(__dirname, 'node_modules', 'react-tether', 'dist', 'react-tether.js'),
        }
    }
};