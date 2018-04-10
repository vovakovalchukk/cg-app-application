define([
	'react',
	'Common/Components/Container',
	'Common/Components/Popup/Message',
	'Product/Components/CreateListing/Form/Ebay',
	'Product/Components/CreateListing/Form/Shopify',
	'Product/Components/CreateListing/Form/BigCommerce',
	'Product/Components/CreateListing/Form/WooCommerce',
	'Common/Components/Select',
	'Product/Utils/CreateListingUtils'
], function (
	React,
	Container,
	PopupMessage,
	EbayForm,
	ShopifyForm,
	BigCommerceForm,
	WooCommerceForm,
	Select,
	CreateListingUtils
) {
		"use strict";

		var channelToFormMap = {
			'ebay': EbayForm,
			'shopify': ShopifyForm,
			'big-commerce': BigCommerceForm,
			'woo-commerce': WooCommerceForm
		};

		var CreateListingPopup = React.createClass({
			getDefaultProps: function () {
				return {
					product: null,
					accounts: {},
					availableChannels: {},
					availableVariationsChannels: {},
					variationsDataForProduct: []
				}
			},
			getInitialState: function () {
				return {
					accountSelected: null,
					productId: null,
					accountId: null,
					title: null,
					description: null,
					price: null,
					weight: null,
					errors: [],
					warnings: [],
					attributeNameMap: {}
				}
			},
			componentDidMount: function () {
				var accountOptions = this.getAccountOptions();

				if (accountOptions.length == 1) {
					this.onAccountSelected(accountOptions[0]);
				}

				if (!this.props.product) {
					return;
				}

				var productDetails = this.props.product.details ? this.props.product.details : {};

				this.setState({
					productId: this.props.product.id,
					title: this.props.product.name,
					description: productDetails.description ? productDetails.description : null,
					price: productDetails.price ? productDetails.price : null,
					weight: productDetails.weight ? productDetails.weight : null
				});
			},
			setFormStateListing: function (listingFormState) {
				this.setState(listingFormState);
			},
			getSelectCallHandler: function (fieldName) {
				return function (selectValue) {
					var newState = {};
					newState[fieldName] = selectValue.value;
					this.setFormStateListing(newState);
				}.bind(this);
			},
			renderCreateListingForm: function () {
				if (!this.state.accountSelected) {
					return;
				}

				var FormComponent = channelToFormMap[this.state.accountSelected.channel];
				return <FormComponent
					{...this.state}
					setFormStateListing={this.setFormStateListing}
					getSelectCallHandler={this.getSelectCallHandler}
					product={this.props.product}
					variationsDataForProduct={this.props.variationsDataForProduct}
					fetchVariations={this.props.fetchVariations}
				/>
			},
			onAccountSelected: function (selectValue) {
				var accountId = selectValue.value;
				var account = this.props.accounts[selectValue.value];

				this.setState({
					accountSelected: account,
					accountId: accountId
				});
			},
			getAccountOptions: function () {
				var options = [];

				var isSimpleProduct = this.props.product.variationCount == 0;
				var accountsAvailableForProductType = isSimpleProduct ? this.props.availableChannels : this.props.availableVariationsChannels;
				for (var accountId in this.props.accounts) {
					var account = this.props.accounts[accountId];
					if (CreateListingUtils.productCanListToAccount(account, accountsAvailableForProductType)) {
						options.push({ name: account.displayName, value: account.id });
					}
				}

				return options;
			},
			submitFormData: function () {
				var formData = this.getFormData();
				$.ajax({
					url: '/products/listing/submit',
					data: formData,
					type: 'POST',
					context: this,
				}).then(function (response) {
					window.scrollTo(0, 0);
					if (response.valid) {
						this.handleFormSubmitSuccess(response);
					} else {
						this.handleFormSubmitError(response);
					}
				}, function (response) {
					n.error('There was a problem creating the listing');
				});
			},
			getFormData: function () {
				var formData = {
					accountId: this.state.accountId,
					productId: this.state.productId,
					listing: {}
				};
				formData.listing = this.getListingDataFromState();

				return formData;
			},
			getListingDataFromState: function () {
				var listing = this.cloneState();
				delete listing.accountSelected;
				delete listing.productId;
				delete listing.accountId;
				delete listing.errors;
				delete listing.warnings;
				return this.mergeAdditionalValuesIntoListingData(listing);
			},
			cloneState: function () {
				return JSON.parse(JSON.stringify(this.state));
			},
			mergeAdditionalValuesIntoListingData: function (listing) {
				if (!listing.additionalValues) {
					return listing;
				}
				for (var key in listing.additionalValues) {
					var values = listing.additionalValues[key];
					for (var key2 in values) {
						var item = values[key2];
						if (!item.name || !item.value) {
							continue;
						}
						if (!listing[key]) {
							listing[key] = {};
						}
						listing[key][item.name] = item.value;
					}
				}
				delete listing.additionalValues;
				return listing;
			},
			handleFormSubmitSuccess: function (response) {
				n.success('Listing created successfully');
				this.props.onCreateListingClose();
			},
			handleFormSubmitError: function (response) {
				this.setState({
					errors: response.errors,
					warnings: response.warnings
				});
			},
			renderErrorMessage: function () {
				if (this.state.errors.length == 0) {
					return;
				}
				var warnings = [];
				if (this.state.warnings) {
					warnings.push(<h4>Warnings</h4>);
					warnings.push(<ul>
						{this.state.warnings.map(function (warning) {
							return (<li>{warning}</li>);
						})}
					</ul>);
				}
				return (
					<PopupMessage
						initiallyActive={!!this.state.errors.length}
						headerText="There were errors when trying to create the listing"
						className="error"
						onCloseButtonPressed={this.onErrorMessageClosed}
					>
						<h4>Errors</h4>
						<ul>
							{this.state.errors.map(function (error) {
								return (<li>{error}</li>);
							})}
						</ul>
						{warnings}
						<p>Please address these errors then try again.</p>
					</PopupMessage>
				);
			},
			onErrorMessageClosed: function () {
				this.setState({
					errors: [],
					warnings: []
				});
			},
			render: function () {
				return (
					<Container
						initiallyActive={true}
						className="editor-popup product-create-listing"
						onYesButtonPressed={this.submitFormData}
						onNoButtonPressed={this.props.onCreateListingClose}
						closeOnYes={false}
						headerText={"Create New Listing"}
						subHeaderText={"ChannelGrabber needs additional information to complete this listing. Please check below and complete all the fields necessary."}
						yesButtonText="Create Listing"
						noButtonText="Cancel"
					>
						<form>
							{this.renderErrorMessage()}
							<div className={"order-form half"}>
								<label>
									<span className={"inputbox-label"}>Select an account to list to:</span>
									<div className={"order-inputbox-holder"}>
										<Select
											options={this.getAccountOptions()}
											selectedOption={
												this.state.accountSelected
													&& this.state.accountSelected.displayName
													? { name: this.state.accountSelected.displayName }
													: null
											}
											onOptionChange={this.onAccountSelected.bind(this)}
											autoSelectFirst={false}
										/>
									</div>
								</label>
								{this.renderCreateListingForm()}
							</div>
						</form>
					</Container>
				);
			}
		});

		return CreateListingPopup;
	});
