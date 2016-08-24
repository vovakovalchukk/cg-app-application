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
                <div className="details-layout-column">
                    <Tabs selected={0}>
                        <Pane label="Stock">
                            <StockView variations={this.props.variations} fullView={this.props.fullView}/>
                        </Pane>
                        <Pane label="Dimensions">
                            <DimensionsView variations={this.props.variations} fullView={this.props.fullView}/>
                        </Pane>
                    </Tabs>
                </div>
            );
        }
    });
    return DetailViewComponent;
});