export default (function fieldService (){
    const VARIATION_ID_PREFIX = 'id-';

    return {
        // reason for prefixing is because redux-form does not accept number only identifiers for name values.
        getVariationIdWithPrefix: function(id){
            return VARIATION_ID_PREFIX + id;
        }
    }
}());