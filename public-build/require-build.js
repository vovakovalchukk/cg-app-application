({
    appDir: "../public/channelgrabber",
    baseUrl: "zf2-v4-ui/js/",
    mainConfigFile: "../public/channelgrabber/zf2-v4-ui/js/main.js",
    dir: "../public/cg-built",
    paths: {
        orders: "../../../../public/channelgrabber/orders",
        Filters: "../../../../public/channelgrabber/filters/js",
    },
    modules: [{
        name: "main"
    }, {
        name: "element/moreButton",
    }, {
        name: "popup/mustache",
    }]
})
