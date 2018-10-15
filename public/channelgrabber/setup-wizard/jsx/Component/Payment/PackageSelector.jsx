import React from 'react';
import SelectComponent from 'Common/Components/Select';


class PackageSelectorComponent extends React.Component {
    static defaultProps = {
        locale: {
            getSelectPackageName: function(packageInfo) {
                return packageInfo.name;
            },
            getPackageInfo: function(selectedPackage) {
                return null;
            }
        },
        phoneNumber: null,
        selectedPackage: false,
        selectedBillingDuration: null,
        packages: [],
        onPackageSelection: null,
        onBillingDurationSelection: null
    };

    state = {
        selected: this.props.selectedPackage,
        billingDuration: this.props.selectedBillingDuration
    };

    getPackages = () => {
        var fromOrderVolume = 0;
        return this.props.packages.map(function(packageInfo) {
            packageInfo.fromOrderVolume = fromOrderVolume;
            fromOrderVolume = packageInfo.orderVolume + 1;
            return packageInfo;
        });
    };

    getSelectedPackage = () => {
        var selectedPackage = this.state.selected;
        var packages = this.getPackages();
        var indexOfSelectedPackage = packages.findIndex(function(packageInfo) {
            return packageInfo.id == selectedPackage;
        });
        return (indexOfSelectedPackage > -1 ? packages[indexOfSelectedPackage] : null);
    };

    getSelectOptions = () => {
        var locale = this.props.locale;
        return this.getPackages().map(function(packageInfo) {
            return {
                name: locale.getSelectPackageName(packageInfo),
                value: packageInfo.id
            };
        });
    };

    getSelectSelectedOption = () => {
        var selectedPackage = this.getSelectedPackage();
        return {
            name: (selectedPackage ? this.props.locale.getSelectPackageName(selectedPackage) : ''),
            value: (selectedPackage ? selectedPackage.id : false)
        };
    };

    selectPackage = (selectedPackage) => {
        this.setState({
            selected: selectedPackage.value
        }, function() {
            if (typeof(this.props.onPackageSelection) === "function") {
                this.props.onPackageSelection(selectedPackage.value)
            }
        });
    };

    renderSelect = () => {
        return (
            <label>
                <span className="inputbox-label">Select monthly order volume:</span>
                <SelectComponent
                    autoSelectFirst={false}
                    options={this.getSelectOptions()}
                    selectedOption={this.getSelectSelectedOption()}
                    onOptionChange={this.selectPackage.bind(this)}
                />
                {this.renderMoreOrders()}
            </label>
        );
    };

    renderMoreOrders = () => {
        if (!this.props.phoneNumber) {
            return null;
        }
        return (
            <span className="moreOrders">Need more orders?<br />Contact us on {this.props.phoneNumber}</span>
        );
    };

    renderPackageDetails = () => {
        var selectedPackage = this.getSelectedPackage();
        if (!selectedPackage) {
            return;
        }
        return this.props.locale.getPackageInfo(selectedPackage, this.state.billingDuration, function(billingDuration) {
            this.setState({
                billingDuration: billingDuration
            }, function() {
                if (typeof(this.props.onBillingDurationSelection) === "function") {
                    this.props.onBillingDurationSelection(billingDuration)
                }
            });
        }.bind(this));
    };

    render() {
        return (
            <div>
                {this.renderSelect()}
                {this.renderPackageDetails()}
            </div>
        );
    }
}

export default PackageSelectorComponent;
