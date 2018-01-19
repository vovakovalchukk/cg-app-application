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
        getDefaultProps: function() {
            return {
                product: null
            }
        },
        render: function() {
            return <div>
                <label>
                    <span className={"inputbox-label"}>Listing Title:</span>
                    <div className={"order-inputbox-holder"}>
                        <Input name='title' value={this.props.product.name ? this.props.product.name : null} />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Price</span>
                    <div className={"order-inputbox-holder"}>
                        <CurrencyInput value={this.props.product.details.price ? this.props.product.details.price : null} />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Description</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name="description"
                            value={this.props.product.details.description ? this.props.product.details.description : null}
                        />
                    </div>
                </label>
            </div>;
        }
    });

    return EbayComponent;
});