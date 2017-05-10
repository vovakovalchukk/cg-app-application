define([
    'react',
    'Common/Components/EditableField'
], function(
    React,
    EditableField
) {
    "use strict";

    var EditorComponent = React.createClass({
        render: function()
        {
            return (
                <div className="purchase-orders-editor">
                    <div className="editor-row">
                        <EditableField initialFieldText="Enter Purchase Order Number" onSubmit={this.props.onNameChange}/>
                    </div>
                    <div className="editor-row">

                    </div>
                </div>
            );
        }
    });

    return EditorComponent;
});