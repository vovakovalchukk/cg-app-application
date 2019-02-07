import React from 'react';
import Popup from 'Common/Components/Popup';


class DeleteCategoryMapComponent extends React.Component {
    static defaultProps = {
        onClick: function () {}
    };

    state = {
        hasPopup: false
    };

    displayConfirmationPopup = () => {
        this.setState({
            hasPopup: true
        })
    };

    onConfirm = () => {
        this.props.onClick();
        this.hidePopup();
    };

    hidePopup = () => {
        this.setState({
            hasPopup: false
        });
    };

    render() {
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
                <label className={"map-button remove-button u-float-left u-margin-left-small"}>
                    <div className={"button"} onClick={this.displayConfirmationPopup}>
                        <span>Delete</span>
                    </div>
                </label>
            </span>
        );
    }
}

export default DeleteCategoryMapComponent;
