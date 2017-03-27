define(['react', 'EmailDesigner/Components/Inspectors/Delete', 'Common/PubSub'], function (React, Delete, PubSub) {
    "use strict";

    var InspectorList = function () {
        this.inspectors = {
            'Delete': function (data) {
                return React.createElement(Delete, { id: data.id });
            },
            'Text': function (data) {
                return React.createElement(Delete, { id: data.id });
            }
        };

        this.defaults = {
            'Delete': function (data) {
                return {
                    id: data.id,
                    topic: 'ELEMENT.DELETE'
                };
            },
            'Text': function (data) {
                return {
                    id: data.id,
                    text: data.text,
                    style: data.style,
                    topic: 'ELEMENT.UPDATE'
                };
            }
        };
    };

    InspectorList.prototype.renderInspector = function (inspectorData) {
        var defaults = this.defaults[inspectorData.type](inspectorData.element);
        return this.inspectors[inspectorData.type](defaults);
    };

    return new InspectorList();
});
