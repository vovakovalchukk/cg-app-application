define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/ChannelBadge',
    'Common/Components/Select',
    'Common/Components/MultiSelect',
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    ChannelBadgeComponent,
    Select,
    MultiSelect
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    var AccountSelectionPopup = React.createClass({
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
                <span className={"inputbox-label"}>Site: </span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        options={this.getSiteSelectOptions()}
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
        onSiteSelected: function(input, site) {
            input.onChange(site.value);
        },
        renderCategorySelectField: function() {
            /** @TODO */
            return null;
        },
        onAccountSelected: function(input, accountId) {
            input.onChange(accountId);
        },
        renderAccountBadge: function(accountData, field) {
            return <ChannelBadgeComponent
                id={accountData.id}
                channel={accountData.channel}
                displayName={accountData.name}
                onClick={this.onAccountSelected.bind(this, field.input)}
            />;
        },
        renderAccountSelect: function() {
            var accountSelects = [],
                index = 0;
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                accountSelects.push(
                    <Field
                        name={"account." + index}
                        component={this.renderAccountBadge.bind(this, account)}
                    />
                );
                index++;
            }
            return (<span>
                {accountSelects}
            </span>);
        },
        renderAccountSelectField: function() {
            return <FieldArray name="accounts" component={this.renderAccountSelect}/>
        },
        renderForm: function() {
            return (
                <form onSubmit={this.props.handleSubmit}>
                    {this.renderSiteSelectField()}
                    {this.renderCategorySelectField()}
                    {this.renderAccountSelectField()}
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
        form: "accountSelection"
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
