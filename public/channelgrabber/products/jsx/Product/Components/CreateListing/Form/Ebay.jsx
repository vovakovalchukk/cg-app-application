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
                <label>
                    <span className={"inputbox-label"}>Listing Title:</span>
                    <div className={"order-inputbox-holder"}><Input /></div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Price</span>
                    <div className={"order-inputbox-holder"}><CurrencyInput /></div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Description</span>
                    <div className={"order-inputbox-holder"}><Input /></div>
                </label>
            </div>;
        }
    });

    return EbayComponent;
});