define([
    'redux',
    'react-redux',
    'redux-form',
    './Component',
    './ActionCreators'
], function(
    Redux,
    ReactRedux,
    ReduxForm,
    Component,
    ActionCreators
) {
    "use strict";
    const mapStateToProps = function(state, ownProps) {
        console.log('in mapStateToProps with ownProps: ', ownProps);
        return {
            fields: getStatePropertyUsingSelectors(state, ownProps.stateSelectors.fields),
            rows: getStatePropertyUsingSelectors(state, ownProps.stateSelectors.rows),
            values: getStatePropertyUsingSelectors(state, ownProps.stateSelectors.values),
        }
    };
    const mapDispatchToProps = function(dispatch) {
        return Redux.bindActionCreators(ActionCreators, dispatch);
    };
    var Connector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return Connector(Component);

    function getStatePropertyUsingSelectors(state,selectors){
        var current = state;
        while(selectors.length){
            if(typeof current !== 'object') return undefined;
            current = current[selectors.shift()];
        }
        console.log("returning state from getSTATE... : " , current);
        return current;
    }
});