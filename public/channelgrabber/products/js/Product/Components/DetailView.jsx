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

    var DetailViewComponent = React.createClass({
        render: function () {
            return (
                <div className="product-details-layout">
                    <Tabs selected={0}>
                        <Pane label="Dimensions">
                            Dimensions
                        </Pane>
                        <Pane label="Stock">
                            Stock
                        </Pane>
                    </Tabs>
                </div>
            );
        }
    });
    return DetailViewComponent;
});