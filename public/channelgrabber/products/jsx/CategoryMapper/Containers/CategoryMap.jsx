define([
    'redux-form',
    'CategoryMapper/Components/CategoryMap'
], function(
    ReduxForm,
    CategoryMap
) {
    var categoryMapFormCreator = ReduxForm.reduxForm({
        form: "categoryMap"
    });
    var CategoryMapContainer = categoryMapFormCreator(CategoryMap);

    return CategoryMapContainer;
});
