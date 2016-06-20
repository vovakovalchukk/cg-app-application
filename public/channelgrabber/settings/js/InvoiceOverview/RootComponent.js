define([
    'react',
    'InvoiceOverview/SectionComponent'
], function(
    React,
    SectionComponent
) {
    "use strict";

    var RootComponent = React.createClass({
        render: function render() {
            console.log("Rendering Root");
            var rootElement = React.createElement('div', {},
                React.createElement(
                    SectionComponent,
                    {
                        dataUrl: '',
                        className: 'invoice-template-section module'
                    },
                    "Create New Invoice"
                ),
                React.createElement(
                    SectionComponent,
                    {
                        dataUrl: '',
                        className: 'invoice-template-section module'
                    },
                    "Edit Existing Invoice"
                )
            );
            return rootElement;
        }
    });

    return RootComponent;
});