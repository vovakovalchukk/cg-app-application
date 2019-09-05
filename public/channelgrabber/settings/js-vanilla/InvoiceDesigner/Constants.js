define([], function() {
    const Constants = (function() {
        let MARGIN_TO_DIMENSION = {
            top: 'height',
            bottom: 'height',
            left: 'width',
            right: 'width'
        };
        let DIMENSION_TO_GRID_TRACK = {
            height: 'rows',
            width: 'columns'
        };
        let GRID_TRACK_TO_DIMENSION = getKeyValueReverse(DIMENSION_TO_GRID_TRACK);

        return {
            MARGIN_TO_DIMENSION,
            DIMENSION_TO_GRID_TRACK,
            GRID_TRACK_TO_DIMENSION
        };
    }());

    return Constants;

    function getKeyValueReverse(forwardObject) {
        let reversed = {};
        for (let key in forwardObject) {
            if (forwardObject.hasOwnProperty(key)) {
                reversed[forwardObject[key]] = key;
            }
        }
        return reversed;
    }
});
