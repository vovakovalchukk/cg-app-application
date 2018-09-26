const path = require('path');

module.exports = {
    mode: "production",
    entry: {
        // Name the entry points after the path you want them to end up in, relative to output.path
        "products/js/Product/Product": "./public/channelgrabber/products/jsx/Product/Product.jsx",
        "products/js/PurchaseOrders/PurchaseOrders": "./public/channelgrabber/products/jsx/PurchaseOrders/PurchaseOrders.jsx",
        "products/js/CategoryMapper/App": "./public/channelgrabber/products/jsx/CategoryMapper/App.jsx",
        "orders/js/ManualOrder/ManualOrder": "./public/channelgrabber/orders/jsx/ManualOrder/ManualOrder.jsx",
        "settings/js/InvoiceOverview/InvoiceOverview": "./public/channelgrabber/settings/js-vanilla/InvoiceOverview/InvoiceOverview.js",
        "setup-wizard/js/Component/Payment/PackageSelector": "./public/channelgrabber/setup-wizard/jsx/Component/Payment/PackageSelector.jsx",
        "setup-wizard/js/Payment/Locale/en-GB": "./public/channelgrabber/setup-wizard/jsx/Payment/Locale/en-GB.jsx",
        "setup-wizard/js/Payment/Locale/en-US": "./public/channelgrabber/setup-wizard/jsx/Payment/Locale/en-US.jsx",
    },
    module: {
        rules:[
            {
                test: /\.jsx?$/,
                loader: 'babel-loader',
                options: {
                    presets: ['es2015', 'react', 'stage-2']
                }
            },
            { test: /thenBy/, use: 'exports-loader?firstBy' },
            { test: /jquery/, use: 'exports-loader?$' },
        ]
    },
    output: {
        path: path.resolve(__dirname, 'public', 'cg-built'),
        filename: "[name].js",
        libraryTarget: "amd",
        // Don't name the AMD modules! Calling code expects anonymous modules
        library: "",
        // Export ES6 modules 'default' value
        libraryExport: "default"
    },
    resolve: {
        modules: [
            path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx'),
            path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'js-vanilla'),
            "node_modules",
            path.resolve(__dirname, 'public', 'channelgrabber', 'zf2-v4-ui', 'js'),
        ],
        extensions: ['.js', '.jsx'],
        alias: {
            // Can't have an alias for Product as it exists in both jsx and js-vanilla :(
            //Product: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'Product'),
            CategoryMapper: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'CategoryMapper'),
            PurchaseOrders: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'PurchaseOrders'),
            ManualOrder: path.resolve(__dirname, 'public', 'channelgrabber', 'orders', 'jsx', 'ManualOrder'),
            InvoiceOverview: path.resolve(__dirname, 'public', 'channelgrabber', 'settings', 'js-vanilla', 'InvoiceOverview'),
            SetupWizard: path.resolve(__dirname, 'public', 'channelgrabber', 'setup-wizard', 'jsx'),
            Common: path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'cg-common', 'dist', 'js', 'Common'),
            jquery: path.resolve(__dirname, 'public', 'channelgrabber', 'zf2-v4-ui', 'js', 'jquery.min.js'),
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