define(['react', 'SetupWizard/Component/Payment/PackageInfo/UK'], function(React, PackageInfo) {
    function Locale()
    {

    }

    Locale.prototype.getSelectPackageName = function(packageInfo)
    {
        return packageInfo.fromOrderVolume + "-" + packageInfo.orderVolume;
    };

    Locale.prototype.getPackageInfo = function(selectedPackage, billingDuration, billingDurationChanged)
    {
        return (
            <PackageInfo
                {...selectedPackage}
                billingDuration={billingDuration}
                billingDurationChanged={billingDurationChanged}
            />
        );
    };

    return Locale;
});