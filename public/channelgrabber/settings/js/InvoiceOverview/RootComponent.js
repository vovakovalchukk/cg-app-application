define([
    'React',
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
                        invoiceData: this.props.system
                    }
                ),
                React.createElement(
                    SectionComponent,
                    {
                        className: 'invoice-template-section module',
                        sectionHeader: 'Edit Existing Invoice',
                        invoiceData: this.props.user
                    }
                )
            );
            return rootElement;
        }
    });

    return RootComponent;
});