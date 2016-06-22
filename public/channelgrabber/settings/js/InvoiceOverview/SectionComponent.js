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
        getInitialState: function() {
            return {invoiceElements: []};
        },
        componentDidMount: function() {
            var sectionType = this.props.sectionType;
            this.serverRequest = $.ajax({
                url: "/settings/invoice/templates/"+sectionType,
                type: 'GET',
                success: function(result) {
                    this.setState({invoiceElements: JSON.parse(result.invoiceTemplateData)});
                }.bind(this),
                error: function (error, textStatus, errorThrown) {
                    return {error: 'There was a problem retrieving '+sectionType+' invoice templates from the server.'};
                }
            });
        },
        componentWillUnmount: function() {
            this.serverRequest.abort();
        },
        render: function render() {
            if (this.state.error !== undefined) {
                // Could refactor this into a notification component
                return React.createElement('div', {className: 'error'}, this.state.error);
            }
            var invoiceElements = [];
            this.state.invoiceElements.map(function(element){
                invoiceElements.push(React.createElement(
                    TemplateComponent,
                    element
                ));
            });
            var invoicesHeader = React.createElement("div", {className: 'heading-large'}, this.props.sectionHeader);
            var invoicesList = React.createElement("div", {}, invoiceElements);
            var invoicesSection = React.createElement("div", {className: 'invoice-template-section module'}, invoicesHeader, invoicesList);
            return invoicesSection;
        }
    });

    return SectionComponent;
});