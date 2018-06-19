define(['react', 'SetupWizard/Component/Payment//BillingPeriod'], function(React, BillingPeriod) {
    function Locale()
    {

    }

    Locale.prototype.getSelectPackageName = function(packageInfo)
    {
        return "< " + (packageInfo.orderVolume / 1000) + " k";
    };

    Locale.prototype.getPackageInfo = function(selectedPackage)
    {
        return (
            <div className="package-info">
                <div>
                    <span>Package:</span>
                    <span>{selectedPackage.band.replace(/\s+\(USA\)$/, '')}</span>
                    <span>
                        <a target="_blank" href="https://www.channelgrabber.com/pricing">What do I get?</a>
                    </span>
                </div>
                <div>
                    <span>Billing Period:</span>
                    <span><BillingPeriod /></span>
                </div>
                <div>
                    <span>Monthly cost:</span>
                    <span>{selectedPackage.price}</span>
                </div>
                <div>
                    <span>Due now:</span>
                    <span>{selectedPackage.price}</span>
                </div>
            </div>
        );
    };

    return Locale;
});