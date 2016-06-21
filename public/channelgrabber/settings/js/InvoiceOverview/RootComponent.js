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
            var rootElement = React.createElement('div', {},
                React.createElement(
                    SectionComponent,
                    {
                        className: 'invoice-template-section module',
                        sectionHeader: 'Create New Invoice',
                        sectionType: 'new'
                    }
                ),
                React.createElement(
                    SectionComponent,
                    {
                        className: 'invoice-template-section module',
                        sectionHeader: 'Edit Existing Invoice',
                        sectionType: 'existing'
                    }
                )
            );
            return rootElement;
        }
    });

    return RootComponent;
});