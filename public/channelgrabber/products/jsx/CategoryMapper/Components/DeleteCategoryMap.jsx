define([
    'react',
    'Common/Components/Popup'
], function(
    React,
    Popup
) {
    "use strict";

    var DeleteCategoryMapComponent = React.createClass({
        getInitialState: function(){
            return {
                hasPopup: false
            }
        },
        getDefaultProps: function() {
            return {
                onClick: function () {}
            }
        },
        displayConfirmationPopup: function() {
            this.setState({
                hasPopup: true
            })
        },
        onConfirm: function() {
            this.props.onClick();
            this.hidePopup();
        },
        hidePopup: function() {
            this.setState({
                hasPopup: false
            });
        },
        render: function() {
            return (
                <span className="delete-container">
                    {this.state.hasPopup &&
                        (<Popup
                            onYesButtonPressed={this.onConfirm}
                            onNoButtonPressed={this.hidePopup}
                            initiallyActive={true}
                        >
                            <p>Do you want to delete this category map?</p>
                            <p>It will be deleted permanently and it cannot be recovered.</p>
                        </Popup>)}
                    <label className={"map-button remove-button"}>
                        <div className={"button"} onClick={this.displayConfirmationPopup}>
                            <span>Delete</span>
                        </div>
                    </label>
                </span>
            );
        }
    });

    return DeleteCategoryMapComponent;
});