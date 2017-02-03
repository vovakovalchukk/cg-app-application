define([
    'react',
    'jquery',
    'InvoiceOverview/TemplateComponent'
], function(
    React,
    $,
    TemplateComponent
) {
    "use strict";

    var SectionComponent = React.createClass({
        render: function render() {
            var invoiceElements = [];
            this.props.invoiceData.map(function(element){
                invoiceElements.push(React.createElement(
                    TemplateComponent,
                    element
                ));
            });
            if (invoiceElements.length === 0) {
               return null;
            }
            var invoicesHeader = React.createElement("div", {className: 'heading-large'}, this.props.sectionHeader);
            var invoicesList = React.createElement("div", {}, invoiceElements);
            var invoicesSection = React.createElement("div", {className: 'invoice-template-section module'}, invoicesHeader, invoicesList);
            return invoicesSection;
        }
    });

    return SectionComponent;
});