define([
    'react',
    'redux',
    'react-dom',
    'react-redux',
    'redux-form',
    'redux-thunk',
], function(
    React,
    Redux,
    ReactDom,
    ReactRedux,
    ReduxForm,
    thunk
) {
    const ProductSearchComponent = React.createClass({
        getDefaultProps: function() {
            return {
                createListingData: {}
            };
        },
        render: function() {
            this.props.renderCreateListingPopup(this.props.createListingData);
            return null;
        }
    });

    return ProductSearchComponent;
});
