define([
    'react',
    'Product/Components/SimpleTabs/Tabs',
    'Product/Components/SimpleTabs/Pane',
    'Product/Components/DimensionsView',
    'Product/Components/StockView'
], function(
    React,
    Tabs,
    Pane,
    DimensionsView,
    StockView
) {
    "use strict";

    var DetailViewComponent = React.createClass({
        render: function () {
            return (
                <div className="product-details-layout">
                    <Tabs selected={0}>
                        <Pane label="Dimensions">
                            <DimensionsView variations={this.props.variations}/>
                        </Pane>
                        <Pane label="Stock">
                            <StockView variations={this.props.variations}/>
                        </Pane>
                    </Tabs>
                </div>
            );
        }
    });
    return DetailViewComponent;
});