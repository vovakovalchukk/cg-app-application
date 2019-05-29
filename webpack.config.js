const path = require('path');
const webpack = require('webpack');
const babelPluginStyledComponents = require('babel-plugin-styled-components').default;

module.exports = {
    mode: "production",
    entry: {
        // Name the entry points after the path you want them to end up in, relative to output.path
        "products/js/Product/Product": "./public/channelgrabber/products/jsx/Product/Product.jsx",
        "products/js/PurchaseOrders/PurchaseOrders": "./public/channelgrabber/products/jsx/PurchaseOrders/PurchaseOrders.jsx",
        "products/js/CategoryMapper/App": "./public/channelgrabber/products/jsx/CategoryMapper/App.jsx",
        "orders/js/ManualOrder/ManualOrder": "./public/channelgrabber/orders/jsx/ManualOrder/ManualOrder.jsx",
        "settings/js/InvoiceOverview/InvoiceOverview": "./public/channelgrabber/settings/js-vanilla/InvoiceOverview/InvoiceOverview.js",
        "settings/js/PickListSettings/PickListSettings": "./public/channelgrabber/settings/jsx/PickListSettings/PickListSettings.jsx",
        "settings/js/Listing/ListingTemplates": "./public/channelgrabber/settings/jsx/Listing/ListingTemplates/ListingTemplates.jsx",
        "setup-wizard/js/Component/Payment/PackageSelector": "./public/channelgrabber/setup-wizard/jsx/Component/Payment/PackageSelector.jsx",
        "setup-wizard/js/Payment/Locale/en-GB": "./public/channelgrabber/setup-wizard/jsx/Payment/Locale/en-GB.jsx",
        "setup-wizard/js/Payment/Locale/en-US": "./public/channelgrabber/setup-wizard/jsx/Payment/Locale/en-US.jsx",
        "zf2-register/js/Components/CompanyDetails/CountySelector": "./public/channelgrabber/zf2-register/jsx/Components/CompanyDetails/CountySelector.jsx",
        "reports/js/Reports/Application": "./public/channelgrabber/reports/es6/Reports/Application.js",
        "walmart/js/Setup/Service": "./public/channelgrabber/walmart/js-vanilla/Setup/Service.js",
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
                options: {
                    plugins: babelPluginStyledComponents,
                    presets: [
                        ["env",
                            {
                                "targets": {
                                    "browsers": [
                                        "last 2 Chrome versions",
                                        "last 2 ff versions"
                                    ]
                                }
                            }
                        ],
                        'react',
                        'stage-2'
                    ]
                }
            },
            {
                test: /jquery/,
                use: 'exports-loader?$'
            },
            {
                test: /thenBy/,
                use: 'exports-loader?firstBy'
            }
        ]
    },
    plugins: [
        new webpack.HotModuleReplacementPlugin()
    ],
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
            path.resolve(__dirname, 'public', 'channelgrabber', 'reports', 'es6'),
            "node_modules",
            path.resolve(__dirname, 'public', 'channelgrabber', 'zf2-v4-ui', 'js')
        ],
        extensions: ['.js', '.jsx'],
        alias: {
            // Can't have an alias for Product as it exists in both jsx and js-vanilla :(
            //Product: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'Product'),
            CategoryMapper: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'CategoryMapper'),
            PurchaseOrders: path.resolve(__dirname, 'public', 'channelgrabber', 'products', 'jsx', 'PurchaseOrders'),
            ManualOrder: path.resolve(__dirname, 'public', 'channelgrabber', 'orders', 'jsx', 'ManualOrder'),
            Reports: path.resolve(__dirname, 'public', 'channelgrabber', 'reports', 'es6', 'Reports'),
            InvoiceOverview: path.resolve(__dirname, 'public', 'channelgrabber', 'settings', 'js-vanilla', 'InvoiceOverview'),
            Settings: path.resolve(__dirname, 'public', 'channelgrabber', 'settings'),
            ListingTemplates: path.resolve(__dirname, 'public', 'channelgrabber', 'settings', 'jsx', 'Listing', 'ListingTemplates'),
            SetupWizard: path.resolve(__dirname, 'public', 'channelgrabber', 'setup-wizard', 'jsx'),
            Common: path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'cg-common', 'dist', 'js', 'Common'),
            jquery: path.resolve(__dirname, 'public', 'channelgrabber', 'zf2-v4-ui', 'js', 'jquery.min.js'),
            // React stuff exists in a few places, specify which to use
            react: path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react', 'umd', 'react.production.min.js'),
            'react-with-addons': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react', 'dist', 'react-with-addons.min.js'),
            'react-dom': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-dom', 'umd', 'react-dom.production.min.js'),
            'react-redux': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-redux', 'dist', 'react-redux.min.js'),
            'redux': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'redux', 'dist', 'redux.min.js'),
            'redux-form': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'redux-form', 'dist', 'redux-form.min.js'),
            'redux-thunk': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'redux-thunk', 'dist', 'redux-thunk.min.js'),
            'react-router': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-router', 'umd', 'react-router.min.js'),
            'react-router-dom': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-router-dom', 'umd', 'react-router-dom.min.js'),
            'react-tether': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'react-tether', 'dist', 'react-tether.js'),
            'ChartJs': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'chart.js', 'dist', 'Chart.bundle.js'),
            'styled-components': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'styled-components', 'dist', 'styled-components.js'),
            'fixed-data-table-2': path.resolve(__dirname, 'public', 'channelgrabber', 'vendor', 'fixed-data-table-2', 'dist', 'fixed-data-table.js')
        }
    }
};
