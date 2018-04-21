define([
    'redux',
    'react-redux',
    './Component',
    './ActionCreators',
], function (
    Redux,
    ReactRedux,
    Component,
    ActionCreators
) {
    "use strict";

    console.log('in Root of createVariationsTable');

    // I want to make sure this is properly referenced

    console.log('want to to log ActionCreators', ActionCreators)


    const mapStateToProps = function(state){
        return{
            variationRowProperties:state.variationRowProperties
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };
    var FormConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return FormConnector(Component);
})