require.config({
    // Set baseUrl for easy access to the modules we are testing
    baseUrl: "../../public/channelgrabber/orders/js/",
    // The paths below are now relative to the baseUrl
    paths: {
        jasq: "../../../../tests/javascript/lib/jasq"
    }
});

require(['../../zf2-v4-ui/js/object-helpers']);

// Configure Jasmine
var jasmineEnv = jasmine.getEnv();
jasmineEnv.updateInterval = 1000;
var htmlReporter = new jasmine.HtmlReporter();
jasmineEnv.addReporter(htmlReporter);
jasmineEnv.specFilter = function (spec) {
    return htmlReporter.specFilter(spec);
};

require([
    "../../../../tests/javascript/spec/invoice-designer/Service"
    ], function ()
    {
        jasmineEnv.execute();
    }
);