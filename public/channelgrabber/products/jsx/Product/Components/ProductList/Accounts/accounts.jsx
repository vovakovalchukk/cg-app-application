define([
    'Product/Components/ProductList/Accounts/reducer'
], function(
    accountsReducer
) {
    "use strict";
    
    let accounts =  (function(){
        return {
            getters: {
                'getAccounts': state => {
                    console.log('state: ',state);
                    
                    
                    return state.accounts.accounts;
                },
                'getFeatures': state=>{
                    return state.accounts.features;
                }
            },
            reducer: accountsReducer
        }
    }());
    
    console.log('accounts: ', accounts);
    
    
    return accounts
});