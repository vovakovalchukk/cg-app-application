import React from 'react';

class Row extends React.Component {
    render() {
        return (
            <label className="clearfix">
                <span className="inputbox-label">{this.props.label}</span>
                <div className={"order-inputbox-holder " + (this.props.className || "")}>
                    {this.props.children}
                </div>
            </label>
        );
    }
}

export default Row