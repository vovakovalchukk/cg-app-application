define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input'
], function(
    React,
    Select,
    CurrencyInput,
    Input
) {
    "use strict";

    var EbayComponent = React.createClass({
        render: function() {
            return <div>
                <div>
                    <div>Listing Title</div>
                    <div><Input /></div>
                </div>
                <div>
                    <div>Price</div>
                    <div><CurrencyInput /></div>
                </div>
                <div>
                    <div>Description</div>
                    <div><Input /></div>
                </div>
            </div>;
        }
    });

    return EbayComponent;
});