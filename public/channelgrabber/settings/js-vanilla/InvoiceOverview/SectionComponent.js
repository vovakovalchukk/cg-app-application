import React from 'react';
import $ from 'jquery';
import TemplateComponent from 'InvoiceOverview/TemplateComponent';

class SectionComponent extends React.Component {
    render() {
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
}

export default SectionComponent;