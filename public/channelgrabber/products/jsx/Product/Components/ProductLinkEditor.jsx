define([
    'react',
    'Common/Components/Popup'
], function(
    React,
    Popup
) {
    "use strict";

    var ProductLinkEditorComponent = React.createClass({
        getDefaultProps: function () {
            return {
                productName: ""
            }
        },
        render: function()
        {
            console.log(!!this.props.productName.length);
            return (
                <Popup
                    initiallyActive={!!this.props.productName.length}
                    onYesButtonPressed={this.props.onYesButtonPressed}
                    onNoButtonPressed={this.props.onNoButtonPressed}
                    headerText={"Select products to link to "+this.props.productName}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                >
                    <div id="product-link-editor">
                        Hello World
                    </div>
                </Popup>
            );
        }
    });

    return ProductLinkEditorComponent;
});