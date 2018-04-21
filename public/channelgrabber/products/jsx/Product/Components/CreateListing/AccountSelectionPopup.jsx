define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/ChannelBadge',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
    'CategoryMapper/Components/CategoryMap'
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    ChannelBadgeComponent,
    Select,
    MultiSelect,
    CategoryMap
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    var AccountSelectionPopup = React.createClass({
        getInitialState: function() {
            return {
                addNewCategoryVisible: false
            }
        },
        renderSiteSelectField: function() {
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                if (account.channel == 'ebay') {
                    return <Field
                        name="site"
                        component={this.renderSiteSelectComponent}
                    />
                }
            }
            return null;
        },
        renderSiteSelectComponent: function(field) {
            return (<label>
                <span className={"inputbox-label"}>Site</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        autoSelectFirst={false}
                        options={this.getSiteSelectOptions()}
                        selectedOption={this.getSelectedSite.call(this, field.input.value)}
                        onOptionChange={this.onSiteSelected.bind(this, field.input)}
                        filterable={true}
                    />
                </div>
            </label>);
        },
        getSiteSelectOptions: function() {
            var options = [];
            for (var siteId in this.props.ebaySiteOptions) {
                options.push({
                    name: this.props.ebaySiteOptions[siteId],
                    value: siteId
                })
            }
            return options;
        },
        getSelectedSite: function (siteId) {
            return {
                name: siteId ? this.props.ebaySiteOptions[siteId] : '',
                value: siteId ? siteId : ''
            }
        },
        onSiteSelected: function(input, site) {
            input.onChange(site.value);
        },
        renderCategorySelectField: function() {
            return <Field
                name="categories"
                component={this.renderCategorySelectComponent}
            />
        },
        renderCategorySelectComponent: function(field) {
            return (<label>
                <span className={"inputbox-label"}>Category </span>
                <div className={"order-inputbox-holder"}>
                    <MultiSelect
                        options={this.getCategorySelectOptions()}
                        onOptionChange={this.onCategorySelected.bind(this, field.input)}
                        filterable={true}
                    />
                </div>
                <a href="#" onClick={this.showAddNewCategoryMapComponent}>Add new category map</a>
            </label>);
        },
        getCategorySelectOptions: function() {
            var options = [];
            for (var categoryId in this.props.categoryTemplateOptions) {
                options.push({
                    name: this.props.categoryTemplateOptions[categoryId],
                    value: categoryId
                });
            }
            return options;
        },
        onCategorySelected: function(input, categories) {
            input.onChange(categories.map(function(category) {
                return category.value;
            }));
        },
        showAddNewCategoryMapComponent: function() {
            this.setState({
                addNewCategoryVisible: true
            });
        },
        renderAccountSelectField: function() {
            return <FieldArray name="accounts" component={this.renderAccountSelect}/>
        },
        renderAccountSelect: function() {
            var accountSelects = [],
                index = 0;
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                accountSelects.push(
                    <Field
                        name={"accounts." + index}
                        component={this.renderAccountBadge.bind(this, account)}
                    />
                );
                index++;
            }
            return (<span>
                {accountSelects}
            </span>);
        },
        renderAccountBadge: function(accountData, field) {
            return <ChannelBadgeComponent
                id={accountData.id}
                channel={accountData.channel}
                displayName={accountData.name}
                onClick={this.onAccountSelected.bind(this, field.input)}
                selected={!!field.input.value}
            />;
        },
        onAccountSelected: function(input, accountId) {
            input.onChange(input.value ? null : accountId);
        },
        renderAddNewCategoryComponent: function() {
            if (!this.state.addNewCategoryVisible) {
                return;
            }
            return <CategoryMap
                accounts={this.props.accounts}
                mapId={0}
            />;
        },
        renderForm: function() {
            return (
                <form
                    onSubmit={this.props.handleSubmit}
                    className="account-select-form"
                >
                    {this.renderAccountSelectField()}
                    {this.renderSiteSelectField()}
                    {this.renderCategorySelectField()}
                    {this.renderAddNewCategoryComponent()}
                </form>
            );
        },
        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup product-create-listing"
                    closeOnYes={false}
                    headerText={"Selects accounts to list to"}
                    onNoButtonPressed={this.props.onCreateListingClose}
                    yesButtonText="Next"
                    noButtonText="Cancel"
                >
                    {this.renderForm()}
                </Container>
            );
        }
    });

    AccountSelectionPopup = ReduxForm.reduxForm({
        form: "accountSelection",
        onSubmit: function() {
            console.log(arguments);
        },
        onChange: function(values) {
            console.log(values);
        }
    })(AccountSelectionPopup);

    var mapStateToProps = function (state, ownProps) {
        return {
            accounts: state.accounts,
            categoryTemplateOptions: state.categoryTemplateOptions
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {}
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(AccountSelectionPopup);
});
