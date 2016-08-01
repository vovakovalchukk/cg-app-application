define([
    'react'
], function(
    React
) {
    "use strict";

    var CheckboxComponent = React.createClass({
        render: function()
        {
            return (
                <div className="checkbox-container">
                    <div className="checkbox-holder bulk-action-checkbox">
                        <a className="std-checkbox">
                            <input type="checkbox" id={"product-checkbox-input-"+this.props.id} name="" />
                            <label htmlFor={"product-checkbox-input-"+this.props.id}>
                                <span className="checkbox_label"></span>
                            </label>
                        </a>
                    </div>
                </div>
            );
        }
    });

    return CheckboxComponent;
});