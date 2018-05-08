define(['react', 'Common/Components/Select'], function(React, SelectComponent) {
    "use strict";

    var PackageSelectorComponent = React.createClass({
        getDefaultProps: function() {
            return {
                selectedPackage: false,
                packages: [],
                onPackageSelection: null
            }
        },
        getInitialState: function() {
            return {
                selected: this.props.selectedPackage
            };
        },
        getPackages: function() {
            var fromOrderVolume = 0;
            return this.props.packages.map(function(packageInfo) {
                packageInfo.fromOrderVolume = fromOrderVolume;
                fromOrderVolume = packageInfo.orderVolume + 1;
                return packageInfo;
            });
        },
        getSelectedPackage: function() {
            var selectedPackage = this.state.selected;
            var packages = this.getPackages();
            var indexOfSelectedPackage = packages.findIndex(function(packageInfo) {
                return packageInfo.id == selectedPackage;
            });
            return (indexOfSelectedPackage > -1 ? packages[indexOfSelectedPackage] : null);
        },
        getSelectPackageName: function(packageInfo) {
            return packageInfo.fromOrderVolume + "-" + packageInfo.orderVolume;
        },
        getSelectOptions: function() {
            var self = this;
            return this.getPackages().map(function(packageInfo) {
                return {
                    name: self.getSelectPackageName(packageInfo),
                    value: packageInfo.id
                };
            });
        },
        getSelectSelectedOption: function() {
            var selectedPackage = this.getSelectedPackage();
            return {
                name: (selectedPackage ? this.getSelectPackageName(selectedPackage) : ''),
                value: (selectedPackage ? selectedPackage.id : false)
            };
        },
        selectPackage: function(selectedPackage) {
            this.setState({
                selected: selectedPackage.value
            }, function() {
                if (this.props.onPackageSelection) {
                    this.props.onPackageSelection(selectedPackage.value)
                }
            });
        },
        renderSelect: function() {
            return (
                <label>
                    <span className="inputbox-label">Select monthly order volume:</span>
                    <SelectComponent
                        autoSelectFirst={false}
                        options={this.getSelectOptions()}
                        selectedOption={this.getSelectSelectedOption()}
                        onOptionChange={this.selectPackage.bind(this)}
                    >
                    </SelectComponent>
                    <span className="moreOrders">Need more orders?<br />Contact us on 01617110248</span>
                </label>
            );
        },
        renderPackageDetails: function() {
            var selectedPackage = this.getSelectedPackage();
            if (!selectedPackage) {
                return;
            }
            return (
                <div className="package-info">
                    <div>
                        <span>Package Needed:</span>
                        <span>{selectedPackage.name}</span>
                    </div>
                    <div>
                        <span>Monthly cost:</span>
                        <span>£{selectedPackage.price} ex VAT</span>
                    </div>
                </div>
            );
        },
        render: function() {
            return (
                <div>
                    {this.renderSelect()}
                    {this.renderPackageDetails()}
                </div>
            );
        }
    });

    return PackageSelectorComponent;
});