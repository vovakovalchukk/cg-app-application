require.config({
    // Set baseUrl for easy access to the modules we are testing
    baseUrl: "../../public/channelgrabber/zf2-v4-ui/js/",
    // The paths below are now relative to the baseUrl
    paths: {
        jasq: "../../../../tests/javascript/lib/jasq",
        jquery: "jquery.min",
        InvoiceDesigner: "../../settings/js/InvoiceDesigner"
    }
});

require(['object-helpers', 'string-helpers']);

// Configure Jasmine
var jasmineEnv = jasmine.getEnv();
jasmineEnv.updateInterval = 1000;
var htmlReporter = new jasmine.HtmlReporter();
jasmineEnv.addReporter(htmlReporter);
jasmineEnv.specFilter = function (spec) {
    return htmlReporter.specFilter(spec);
};

require([
    "./spec/InvoiceDesigner/Application.js",
    "./spec/InvoiceDesigner/CollectionAbstract.js",
    "./spec/InvoiceDesigner/EntityHydrateAbstract.js",
    "./spec/InvoiceDesigner/Module/TemplateSelector.js",
    "./spec/InvoiceDesigner/PubSubAbstract.js",
    "./spec/InvoiceDesigner/Template/Entity.js",
    "./spec/InvoiceDesigner/Template/Inspector/Service.js",
    "./spec/InvoiceDesigner/Template/Mapper.js",
    "./spec/InvoiceDesigner/Template/Service.js",
    "./spec/InvoiceDesigner/Template/Storage/Ajax.js"
    ], function ()
    {
        jasmineEnv.execute();
    }
);
