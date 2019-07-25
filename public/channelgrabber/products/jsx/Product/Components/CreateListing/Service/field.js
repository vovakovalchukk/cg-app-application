export default (function fieldService (){
    const VARIATION_ID_PREFIX = 'id-';

    return {
        // reason for prefixing is due to redux-form not liking number only identifiers for name values.
        getVariationIdWithPrefix: function(id){
            return VARIATION_ID_PREFIX+id;
        }
    }
}());