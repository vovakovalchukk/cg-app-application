import React from 'react';
import InspectorList from 'EmailDesigner/Components/InspectorList';
import PubSub from 'Common/PubSub';


class ElementInspectorComponent extends React.Component {
    static defaultProps = {
        element: {
            inspectors: []
        }
    };

    componentDidMount() {
        //PubSub.subscribe('ELEMENT.SELECTED', this.elementSubscriber);
    }

    componentWillUnmount() {
        //PubSub.unsubscribe(this.elementSubscriber);
    }

    elementSubscriber = (msg, data) => {
        // this.setState({
        //     inspectors: data.inspectors
        // });
    };

    render() {
        return (
            <div className="sidebar sidebar-fixed sidebar-right sidebar-email-designer">
                {this.props.element.inspectors.map(function (inspectorName) {
                    return InspectorList.renderInspector({type: inspectorName, element: this.props.element});
                }.bind(this))}
            </div>
        );
    }
}

export default ElementInspectorComponent;
