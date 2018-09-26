import React from 'react';


class CategoryFormComponent extends React.Component {
    static defaultProps = {
        title: null,
        fieldValues: {},
        channelForm: null,
        categoryId: null,
        accountId: null,
        product: {},
        variationsDataForProduct: [],
        fieldChange: null,
        resetSection: null,
        selectedProductDetails: {}
    };

    render() {
        var ChannelForm = this.props.channelForm;
        return (
            <div className="category-form-container">
                <h2>Category: {this.props.title}</h2>
                <ChannelForm
                    categoryId={this.props.categoryId}
                    accountId={this.props.accountId}
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    fieldChange={this.props.fieldChange}
                    resetSection={this.props.resetSection}
                    selectedProductDetails={this.props.selectedProductDetails}
                    {...this.props.fieldValues}
                    {...this.props}
                />
            </div>
        );
    }
}

export default CategoryFormComponent;
