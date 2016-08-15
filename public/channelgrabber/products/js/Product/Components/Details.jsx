define([
    'react',
    'Product/Components/SimpleTabs/Tabs',
    'Product/Components/SimpleTabs/Pane'
], function(
    React,
    Tabs,
    Pane
) {
    "use strict";

    var DetailsComponent = React.createClass({
        render: function()
        {
            /**
             *  Check listings count
             *      If listings > 1
             *          Add variations header to main header
             *          set parent to true
             *
             *  Build header row
             *
             *  Add header row
             *
             *  Foreach listing
             *      if parent then append variation data
             *      Build details data
             *      build stock data
             *      Add row to main view
             *
             *  Return View
             *
             */
            var variationsView = "";
            var isParentProduct = false;
            if (this.props.listings.length > 1) {
                isParentProduct = true;
                //  Add variations table
                variationsView = (
                    <div className="variation-table">
                        <table>
                            <thead>
                            <tr>
                                <th>Image</th>
                                <th>SKU</th>
                                <th>chicken</th>
                                <th>daniel</th>
                                <th>Size</th>
                                <th>Variance</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><img src="/cg-built/products/img/noproductsimage.png" height="23"/></td>
                                    <td>0</td>
                                    <td>little</td>
                                    <td>kjui</td>
                                    <td>Green</td>
                                    <td>big</td>
                                </tr>
                                <tr>
                                    <td><img src="http://youraccount.ekmpowershop23.com/ekmps/shops/channelgrabber/images/discounttest-11-p.jpg" height="23"/></td>
                                    <td>SINGLEQA</td>
                                    <td>big</td>
                                    <td>kjui</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                );
            }
            return (<div className="product-content-container">
                <div className="variations-layout-column">
                    {variationsView}
                </div>
                <div className="product-details-layout">
                    <Tabs selected={0}>
                        <Pane label="Dimensions">
                            {variationsView}
                        </Pane>
                        <Pane label="Stock">
                            <div>This is my tab 2 contents!</div>
                        </Pane>
                    </Tabs>
                </div>
            </div>);
        }
    });

    return DetailsComponent;
});