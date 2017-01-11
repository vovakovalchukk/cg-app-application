define([
    'react',
    'EmailDesigner/Components/Inspectors/Base'
], function(
    React,
    BaseInspector
) {
    "use strict";

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

    return DeleteComponent;
});