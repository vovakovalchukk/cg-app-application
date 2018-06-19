define(['react', 'SetupWizard/Component/Payment/PackageInfo/US'], function(React, PackageInfo) {
    function Locale()
    {

    }

    Locale.prototype.getSelectPackageName = function(packageInfo)
    {
        return "< " + (packageInfo.orderVolume / 1000) + " k";
    };

    Locale.prototype.getPackageInfo = function(selectedPackage)
    {
        return (<PackageInfo {...selectedPackage} />);
    };

    return Locale;
});