import React from 'react';
import BaseInspector from 'EmailDesigner/Components/Inspectors/Base';


class DeleteComponent extends React.Component {
    render() {
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
}

export default DeleteComponent;
