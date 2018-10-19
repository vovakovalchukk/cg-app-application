import React from 'react';
import ReduxForm from 'redux-form';
import Input from 'Common/Components/Input';
import CurrencyInput from 'Common/Components/CurrencyInput';
import ShippingService from './Ebay/ShippingService';
import VariationImagePicker from './Ebay/VariationImagePicker';
import Validators from '../../Validators';


var Field = ReduxForm.Field;

class EbayChannelFormComponent extends React.Component {
    static defaultProps = {
        shippingMethods: {},
        product: {},
        variationsDataForProduct: {},
        currency: ""
    };

    renderDispatchTimeMax = (field) => {
        return this.renderInput('Dispatch Time Max', field, 'What is the longest amount of time it may take you to dispatch an item?');
    };

    renderShippingPrice = (field) => {
        return (
            <label>
                <span className={"inputbox-label"}>{"Shipping Price"}</span>
                <div className={"order-inputbox-holder"}>
                    <CurrencyInput
                        {...field.input}
                        currency={this.props.currency}
                        title="How much you want to charge for shipping"
                        min={0}
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </label>
        );
    };

    renderInput = (label, field, tooltip) => {
        return (
            <label>
                <span className={"inputbox-label"}>{label}</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        {...field.input}
                        inputType="number"
                        title={tooltip}
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </label>
        );
    };

    renderVariationImagePicker = () => {
        if (this.props.variationsDataForProduct.length === 1) {
            return null;
        }
        return <VariationImagePicker
            product={this.props.product}
            variationsDataForProduct={this.props.variationsDataForProduct}
        />
    };

    render() {
        return (
            <div className="ebay-channel-form-container channel-form-container">
                {this.renderVariationImagePicker()}
                <Field name="dispatchTimeMax" component={this.renderDispatchTimeMax} validate={Validators.required} />
                {/** We have to hide the shipping service and shipping price, as new we will show a per category
                 shipping policy instead. We don't remove it completely as we might implement it again later */}
                {/*<ShippingService shippingServices={this.props.shippingMethods} />*/}
                {/*<Field name="shippingPrice" component={this.renderShippingPrice} validate={Validators.required} />*/}
            </div>
        );
    }
}

export default EbayChannelFormComponent;
