const path = require('path');

module.exports = {
    mode: "development",
    entry: {
        // Name the entry points after the path you want them to end up in, relative to output.path
        "products/js/Product/Product": "./public/channelgrabber/products/jsx/Product/Product.jsx"
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
        path: path.resolve(__dirname, 'public', 'cg-built'),
        filename: "[name].js",
        libraryTarget: "amd",
        // Don't name the AMD modules! Calling code expects anonymous modules
        library: ""
        // Add the below once ALL the entry points are converted to ES6 modules
        //,libraryExport: "default"
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
            Common: path.resolve(__dirname, 'node_modules', 'cg-common', 'dist', 'js', 'Common'),
            // React stuff exists in a few places, specify which to use
            react: path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react', 'dist', 'react.min.js'),
            'react-with-addons': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react', 'dist', 'react-with-addons.min.js'),
            'react-dom': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-dom', 'dist', 'react-dom.min.js'),
            'react-redux': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-redux', 'dist', 'react-redux.min.js'),
            'redux': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'redux', 'dist', 'redux.min.js'),
            'redux-form': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'redux-form', 'dist', 'redux-form.min.js'),
            'redux-thunk': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'redux-thunk', 'dist', 'redux-thunk.min.js'),
            'react-router': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-router', 'umd', 'react-router.min.js'),
            'react-router-dom': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-router-dom', 'umd', 'react-router-dom.min.js'),
            'react-tether': path.resolve(__dirname, 'public', 'channelgrabber', 'zf2-v4-ui', 'js', 'react-tether.js'),
            // For some reason react-tether names its dependencies differently for CommonJS and webpack normalises to CommonJS
            React: 'react',
            ReactDOM: 'react-dom',
            Tether: 'tether'
        }
    }
};