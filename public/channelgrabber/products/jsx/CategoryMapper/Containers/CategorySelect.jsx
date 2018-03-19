define([
    'react',
    'react-redux',
    'CategoryMapper/Actions/Category',
    'CategoryMapper/Components/AccountCategorySelect'
], function(
    React,
    ReactRedux,
    Actions,
    AccountCategorySelectComponent
) {
    var mapStateToProps = function(state, ownProps) {
        console.log(state, ownProps);
        return {
            children: state.hasOwnProperty('categories') ? state.categories : {}
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {
            onOptionChange: function(accountId, categoryId, categoryLevel) {
                dispatch(Actions.categorySelected(dispatch, accountId, categoryId, categoryLevel));
            }
        };
    };

    var CategorySelectConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    return CategorySelectConnector(AccountCategorySelectComponent);
});