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
    const mapStateToProps = function(state){
        return{
            variationsTable:state.variationsTable,
            uploadedImages:state.uploadedImages
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };
    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);
})