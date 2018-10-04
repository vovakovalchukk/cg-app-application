let utility = (function() {
    return {
        findDifferenceOfTwoArrays: (arr1, arr2) => {
            return arr1.filter(x => !arr2.includes(x));
        }
    }
}());

export default utility;