define([
    'react',
    'InvoiceOverview/TemplateComponent'
], function(
    React,
    TemplateComponent
) {
    "use strict";

    var SectionComponent = React.createClass({
        getInitialState: function() {
            console.log("Type is "+this.props.sectionType);
            var newInvoices = [
                {
                    name: 'FPS-3 Template',
                    key: 'fps3',
                    imageUrl: '/cg-built/settings/img/InvoiceOverview/TemplateThumbnails/Form-FPS3.png',
                    links: [
                        {
                            name: 'Create',
                            url: '/settings/invoice/fetch'
                        },
                        {
                            name: 'Buy Label',
                            url: 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link'
                        }
                    ],
                    invoiceData: {
                        id: 'default-formsPlusFPS-3_OU1'
                    }
                },
                {
                    name: 'FPS-15 Template',
                    key: 'fps15',
                    imageUrl: '/cg-built/settings/img/InvoiceOverview/TemplateThumbnails/Form-FPS15.png',
                    links: [
                        {
                            name: 'Create',
                            url: '/settings/invoice/fetch'
                        }
                    ],
                    invoiceData: {
                        id: 'default-formsPlusFPS-15_OU1'
                    }
                },
                {
                    name: 'FPS-16 Template',
                    key: 'fps16',
                    imageUrl: '/cg-built/settings/img/InvoiceOverview/TemplateThumbnails/Form-FPS16.png',
                    links: [
                        {
                            name: 'Create',
                            url: '/settings/invoice/fetch'
                        }
                    ],
                    invoiceData: {
                        id: 'default-formsPlusFPS-16_OU1'
                    }
                }
            ];
            return {newInvoices: newInvoices};
        },
        render: function render() {
            var invoiceElements = [];
            this.state.newInvoices.map(function(element){
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