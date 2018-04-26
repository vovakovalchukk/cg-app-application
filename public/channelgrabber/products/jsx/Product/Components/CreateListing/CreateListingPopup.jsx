define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    var CreateListingPopup = React.createClass({
        render: function() {
            console.log(this.props);
            return null;
        }
    });

    var mapStateToProps = null;

    var mapDispatchToProps = null;

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
});
