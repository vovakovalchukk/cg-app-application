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
        getInitialState: function()
        {
            //  Retrieve list of products via ajax and save to state
        },
        render: function()
        {
            return
            <ul>
                <li><ParentProduct/></li>
                <li><SimpleProduct/></li>
            </ul>;
        }
    });

    return ListComponent;
});