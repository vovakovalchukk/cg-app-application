import React from 'react';
import BaseInspector from 'EmailDesigner/Components/Inspectors/Base';
    

    var DeleteComponent = React.createClass({
        render: function() {
            return (
                <BaseInspector
                    className="delete-inspector"
                    heading={this.props.heading}
                >
                    <div className="button" onClick={this.props.onAction}>
                        <span className="title">Delete</span>
                    </div>
                </BaseInspector>
            );
        }
    });

    export default DeleteComponent;
