define([
    'react',
    'Common/Components/EditableField',
    'Common/Components/Button'
], function(
    React,
    EditableField,
    Button
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
                        <Button onClick={this.props.onCompleteClicked} sprite="sprite-dispatch-22-black" text="Complete"/>
                        <Button onClick={this.props.onDownloadClicked} sprite="sprite-download-22-black" text="Download"/>
                        <Button onClick={this.props.onDeleteClicked} sprite="sprite-delete-20-black" text="Delete"/>
                        <Button onClick={this.props.onSaveClicked} sprite="sprite-dispatch-22-black" text="Save"/>
                    </div>
                </div>
            );
        }
    });

    return EditorComponent;
});