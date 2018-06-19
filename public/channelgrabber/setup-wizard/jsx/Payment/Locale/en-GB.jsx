define(['react', 'SetupWizard/Component/Payment//BillingPeriod'], function(React, BillingPeriod) {
    function Locale()
    {

    }

    Locale.prototype.getSelectPackageName = function(packageInfo)
    {
        return packageInfo.fromOrderVolume + "-" + packageInfo.orderVolume;
    };

    Locale.prototype.getPackageInfo = function(selectedPackage)
    {
        return (
            <div className="package-info">
                <div>
                    <span>Package Needed:</span>
                    <span>{selectedPackage.name}</span>
                </div>
                <div>
                    <span>Billing Period:</span>
                    <span><BillingPeriod /></span>
                </div>
                <div>
                    <span>Monthly cost:</span>
                    <span>{selectedPackage.price} ex VAT</span>
                </div>
            </div>
        );
    };

    return Locale;
});