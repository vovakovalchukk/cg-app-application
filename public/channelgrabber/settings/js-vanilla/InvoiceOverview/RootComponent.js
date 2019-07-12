import React from 'react';
import SectionComponent from 'InvoiceOverview/SectionComponent';

class RootComponent extends React.Component {
    render() {
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
}

export default RootComponent;
