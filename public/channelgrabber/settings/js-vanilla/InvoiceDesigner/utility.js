define((function() {
    const utility = function() {
        return {
            createPrototype: function(entities) {
                let combinedPrototype = {};
                for (let entity of entities) {
                    for (let key in entity.prototype) {
                        combinedPrototype[key] = entity.prototype[key];
                    }
                }
                return combinedPrototype;
            },
            // apply caution when using this as all non JSON properties will be lost after conversion
            deepClone: function(object) {
                return JSON.parse(JSON.stringify(object));
            }
        };
    };

    return utility;
}()));