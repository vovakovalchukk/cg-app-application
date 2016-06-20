define([
    'react',
    'InvoiceOverview/TemplateComponent'
], function(
    React,
    TemplateComponent
) {
    "use strict";

    var SectionComponent = React.createClass({
        getIniitalState: function() {
            console.log("AJAX request sent!");
            var newInvoices = [
                {
                    name: 'FPS-16 Template',
                    id: 'fps16',
                    links: [
                        {
                            name: 'Create',
                            url: 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link'
                        },
                        {
                            name: 'Purchase Label',
                            url: 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link'
                        }
                    ]
                },
                {
                    name: 'FPS-15 Template',
                    id: 'fps15',
                    links: [
                        {
                            name: 'Create',
                            url: 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link'
                        }
                    ]
                },
                {
                    name: 'FPS-14 Template',
                    id: 'fps14',
                    links: [
                        {
                            name: 'Create',
                            url: 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link'
                        }
                    ]
                }
            ];
            var newInvoiceElements = [];
            newInvoices.map(function(element){
                newInvoiceElements.push(React.createElement(TemplateComponent, element));
            });
            return {newInvoices: newInvoiceElements};
        },
        render: function render() {
            console.log("Rendering Section");
            var newInvoicesHeader = React.createElement("div", {className: 'heading-large'}, "Create New Invoice");
            var newInvoicesElements = React.createElement("div", {}, this.state.newInvoices);
            var newInvoices = React.createElement(
                "div",
                {className: 'invoice-template-section module'},
                newInvoicesHeader,
                newInvoicesElements
            );
            return newInvoices;
        }
    });

    return SectionComponent;
});