import React from 'react';
import PackageInfo from 'SetupWizard/Component/Payment/PackageInfo/US';
    function Locale()
    {

    }

    Locale.prototype.getSelectPackageName = function(packageInfo)
    {
        return "< " + (packageInfo.orderVolume / 1000) + " k";
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

    export default Locale;
