import React from 'react';
import ReduxForm from 'redux-form';
import ReactRedux from 'react-redux';
import Button from 'Common/Components/Button';
import Actions from 'CategoryMapper/Actions/Actions';
import AccountCategorySelect from 'CategoryMapper/Components/AccountCategorySelect';
import DeleteCategoryMap from 'CategoryMapper/Components/DeleteCategoryMap';


var Field = ReduxForm.Field;
var FieldArray = ReduxForm.FieldArray;

class CategoryMapComponent extends React.Component {
    static defaultProps = {
        handleSubmit: null,
        accounts: {},
        mapId: null,
        closeButtonVisible: false,
        onCloseButtonPressed: function() {}
    };

    renderErrorMessageForCategory = (accountId, field) => {
        var valid = field.meta.valid,
            error = field.meta.error ? JSON.parse(field.meta.error) : false;

        if (valid || !error) {
            return null;
        }

        var existingMapName = error && error.existingMapNames && accountId in error.existingMapNames ? accountId in error.existingMapNames : false;
        if (existingMapName) {
            return <span className="input-error">
                {error.text}
                <div><a href="#" onClick={this.props.onViewExistingMapClick.bind(this, error.existingMapNames[accountId], 1)}>Click here</a> to view it.</div>
            </span>
        }

        var accountIds = error && error.accountIds && accountId in error.accountIds;
        if (accountIds) {
            return <span className="input-error">{error.text}</span>
        }

        return null;
    };

    renderAccountCategorySelectComponent = (accountId, field) => {
        var accountData = this.props.accounts[accountId];
        return <label>
            <span
                className={"inputbox-label"}>{accountData.displayName}
            </span>
            <AccountCategorySelect
                {...field}
                accountId={accountId}
                categories={accountData.categories}
                refreshing={accountData.refreshing}
                refreshable={accountData.refreshable}
                selectedCategories={accountData.selectedCategories ? accountData.selectedCategories : []}
                onCategorySelected={this.props.onCategorySelected.bind(this, this.props.mapId)}
                onRefreshClick={this.props.onRefreshClick}
                onRemoveButtonClick={this.props.onRemoveButtonClick.bind(this, this.props.mapId)}
            />
            {this.renderErrorMessageForCategory(accountId, field)}
        </label>;
    };

    renderCategorySelects = () => {
        var selects = [];
        for (var accountId in this.props.accounts) {
            var account = this.props.accounts[accountId];
            if (!account.channel || !account.displayName) {
                continue;
            }
            selects.push(
                <FieldArray
                    component={this.renderAccountCategorySelectComponent.bind(this, accountId)}
                    name={"categories"}
                />
            );
        };
        return <div className={"category-selects-container"}>
            {selects}
        </div>;
    };

    renderNameField = () => {
        return <Field
            name={'name'}
            component={this.renderNameComponent}
            type="text"
        />
    };

    renderNameComponent = (field) => {
        var type = field.type,
            touched = field.meta.touched,
            error = field.meta.error;

        return <label>
            <div className={"order-inputbox-holder"}>
                <input
                    {...field.input}
                    type={type}
                    placeholder="Category template name here..."
                />
            </div>
            {touched && error && (
                <span className="input-error">{error}</span>
            )}
        </label>;
    };

    renderSaveButton = () => {
        return <label className={"map-button save-button"}>
            <div className={"button" + (this.props.submitting ? " disabled" : "")} onClick={this.onSaveButtonClick}>
                <span>Save</span>
            </div>
        </label>
    };

    renderDeleteButton = () => {
        if (this.props.mapId > 0) {
            return <DeleteCategoryMap
                onClick={this.props.deleteCategoryMap.bind(this, this.props.mapId)}
            />
        }
        return null;
    };

    renderCloseButton = () => {
        if (!this.props.closeButtonVisible) {
            return null;
        }
        return <label className={"map-button save-button"}>
            <div className={"button"} onClick={this.props.onCloseButtonPressed}>
                <span>Close</span>
            </div>
        </label>
    };

    renderFormErrorMessage = () => {
        if (!this.props.error) {
            return null;
        }
        return <span className="input-error form-error">{this.props.error}</span>;
    };

    onSaveButtonClick = () => {
        if (this.props.submitting) {
            return;
        }
        this.props.handleSubmit();
    };

    render() {
        return (
            <form onSubmit={this.props.handleSubmit}>
                <div className={"order-form half product-container category-map-container"}>
                    <div className={"header-container"}>
                        {this.renderNameField()}
                        {this.renderCloseButton()}
                        {this.renderSaveButton()}
                        {this.renderDeleteButton()}
                    </div>
                    {this.renderCategorySelects()}
                    {this.renderFormErrorMessage()}
                </div>
            </form>
        );
    }
}

var categoryMapFormCreator = ReduxForm.reduxForm({
    enableReinitialize: true,
    onChange: (values, dispatch, props) => {
        if (props.error) {
            dispatch(ReduxForm.clearSubmitErrors(props.form));
        }
    }
});

CategoryMapComponent = categoryMapFormCreator(CategoryMapComponent);

var getSelectedLeafCategoriesByAccount = function(categoryMap) {
    var categoriesForAccount,
        categoryId,
        categories = [];

    for (var accountId in categoryMap.selectedCategories) {
        categoriesForAccount = categoryMap.selectedCategories[accountId];
        categoryId = categoriesForAccount[categoriesForAccount.length - 1];
        categories[accountId] = categoryId;
    }

    return categories;
}

var convertCategoryMapToFormData = function(categoryMap) {
    return {
        name: categoryMap.name,
        etag: categoryMap.etag,
        categories: getSelectedLeafCategoriesByAccount(categoryMap)
    }
}

var mapStateToProps = function (state, ownProps) {
    return {
        form: 'categoryMap-' + ownProps.mapId,
        initialValues: ownProps.mapId in state.initialValues ? convertCategoryMapToFormData(state.initialValues[ownProps.mapId]) : {}
    }
};

var mapDispatchToProps = function (dispatch) {
    return {
        onCategorySelected: function(mapId, accountId, categoryId, categoryLevel, selectedCategories) {
            dispatch(Actions.categorySelected(dispatch, mapId, accountId, categoryId, categoryLevel, selectedCategories));
        },
        onRefreshClick: function(accountId) {
            dispatch(Actions.refreshButtonClicked(dispatch, accountId));
        },
        onRemoveButtonClick: function(mapId, accountId) {
            dispatch(Actions.removeButtonClicked(mapId, accountId));
        },
        deleteCategoryMap: function(mapId) {
            dispatch(Actions.deleteCategoryMap(dispatch, mapId));
        }
    };
};

var CategoryMapConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
export default CategoryMapConnector(CategoryMapComponent);

