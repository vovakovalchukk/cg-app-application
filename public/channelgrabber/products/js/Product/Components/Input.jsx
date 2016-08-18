define([
    'react'
], function(
    React
) {
    "use strict";

    var InputComponent = React.createClass({
        render: function () {
            return (
                <div className="detail-text-holder">
                    <div className="submit-input active">
                        <input type={this.props.type} className="submit-inputbox product-detail" placeholder="" value={this.props.value} name={this.props.name} step="0.1" />
                        <div className="edit-btn">
                            <ul>
                                <li><span className="edit" onClick={this.editInput}></span></li>
                            </ul>
                        </div>
                        <div className="submit-cancel">
                            <ul>
                                <li><span className="submit" onClick={this.submitInput}></span></li>
                                <li><span className="cancel" onClick={this.cancelInput}></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return InputComponent;
});