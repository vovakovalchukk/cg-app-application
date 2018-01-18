define([
    'react',
    'Common/Components/Popup'
], function(
    React,
    Popup
) {
    "use strict";

    var CreateListingPopupComponent = React.createClass({
        getDefaultProps: function() {
            return {
                productId: null
            }
        },
        render: function()
        {
            return (
                <Popup
                    initiallyActive={!!this.props.productId}
                    className="editor-popup"
                    onYesButtonPressed={() => {}} /* TODO repeal and replace these 2 lines, too many ES6 */
                    onNoButtonPressed={() => {}}
                    headerText={"make listings"}
                    yesButtonText="Save"
                    noButtonText="Cancel"
                >
                    <h1>Create Listings!!!</h1>
                </Popup>
            );
        }
    });

    return CreateListingPopupComponent;
});