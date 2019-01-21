import React from 'react';
"use strict";

class CheckboxComponent extends React.Component {
    static defaultProps = {
        onClick: null,
        isChecked: null
    };

    render() {
        return (
            <div className="checkbox-container">
                <div className="checkbox-holder bulk-action-checkbox">
                    <a className="std-checkbox">
                        <input
                            id={"product-checkbox-input-" + this.props.id}
                            name=""
                            onClick={this.props.onClick}
                            checked={this.props.isChecked}
                        />
                        <label htmlFor={"product-checkbox-input-" + this.props.id}>
                            <span className="checkbox_label"></span>
                        </label>
                    </a>
                </div>
            </div>
        );
    }
}

export default CheckboxComponent;