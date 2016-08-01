define([
    'react',
    'Product/Components/ParentProduct',
    'Product/Components/SimpleProduct'
], function(
    React,
    ParentProduct,
    SimpleProduct
) {
    "use strict";

    var ListComponent = React.createClass({
        render: function()
        {
            return (
                <div id="products-list">
                    {this.props.products.map(function(object) {
                        if (object.parentProductId > 0) {
                            return <ParentProduct key={object.id} data={object} />
                        } else {
                            return <SimpleProduct key={object.id} data={object}/>;
                        }
                    })}
                </div>
            );

        }
    });

    return ListComponent;
});