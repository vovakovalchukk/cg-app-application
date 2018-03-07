define(['react', 'react-redux', 'Redux/Actions/Todo', 'Redux/Components/FilterLink'], function (React, ReactRedux, Actions, FilterLinkComponent) {
    var mapStateToProps = function (state, ownProps) {
        return {
            active: state.visibilityFilter == ownProps.filter
        };
    };

    var mapDispatchToProps = {
        onClick: Actions.visibility
    };

    var FilterLinkConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return FilterLinkConnector(FilterLinkComponent);
});
