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
            }
        };
    };

    return utility;
}()));