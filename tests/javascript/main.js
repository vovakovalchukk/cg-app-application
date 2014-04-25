require.config({
    // Set baseUrl for easy access to the modules we are testing
    baseUrl: "../../public/channelgrabber/orders/js/",
    // The paths below are now relative to the baseUrl
    paths: {
        jasq: "../../../../tests/javascript/lib/jasq",
        jQuery: "../../zf2-v4-ui/js/jquery.min"
    }
});

// Configure Jasmine
var jasmineEnv = jasmine.getEnv();
jasmineEnv.updateInterval = 1000;
var htmlReporter = new jasmine.HtmlReporter();
jasmineEnv.addReporter(htmlReporter);
jasmineEnv.specFilter = function (spec) {
    return htmlReporter.specFilter(spec);
};

require([
    "../../../../tests/javascript/spec/InvoiceDesigner/Application",
    "../../../../tests/javascript/spec/InvoiceDesigner/Module/TemplateSelector",
    "../../../../tests/javascript/spec/InvoiceDesigner/Template/Mapper",
    "../../../../tests/javascript/spec/InvoiceDesigner/Template/Service",
    "../../../../tests/javascript/spec/InvoiceDesigner/Template/Storage/Ajax"
    ], function ()
    {
        jasmineEnv.execute();
    }
);