import React from 'react';
import PackageInfo from 'SetupWizard/Component/Payment/PackageInfo/UK';
    function Locale()
    {

    }

    Locale.prototype.getSelectPackageName = function(packageInfo)
    {
        return packageInfo.fromOrderVolume + "-" + packageInfo.orderVolume;
    };

    Locale.prototype.getPackageInfo = function(selectedPackage, billingDuration, billingDurationChangeAllowed, billingDurationChanged)
    {
        return (
            <PackageInfo
                {...selectedPackage}
                billingDuration={billingDuration}
                billingDurationChangeAllowed={billingDurationChangeAllowed}
                billingDurationChanged={billingDurationChanged}
            />
        );
    };

    export default Locale;
