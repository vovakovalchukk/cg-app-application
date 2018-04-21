define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/ChannelBadge',
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    ChannelBadgeComponent
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    var AccountSelectionPopup = React.createClass({
        renderSiteSelectField: function() {
            /** @TODO */
        },
        renderCategorySelectField: function() {
            /** @TODO */
        },
        renderAccountBadge: function(accountData, field) {
            console.log(field);
            return <ChannelBadgeComponent
                id={accountData.id}
                channel={accountData.channel}
                displayName={accountData.name}
                onClick={function(accountId) {
                    field.input.onChange(accountId);
                }}
            />;
        },
        renderAccountSelect: function(fields) {
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
        renderForm: function() {
            return (
                <form onSubmit={this.props.handleSubmit}>
                    <FieldArray name="accounts" component={this.renderAccountSelect}/>
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
            channelBadges: state.channelBadges
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {}
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(AccountSelectionPopup);
});
