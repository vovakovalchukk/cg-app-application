
define([

], function(

) {
    "use strict";
  
    
    let tableDataWrapper = function() {
        var self = {};
        
        return {
            storeData:function(data){
              self.data = data;
            },
            getData : function(data) {
                // console.log('in getData: ' , data);
                return self.data;
            }
        };
    };
    
    return tableDataWrapper()
    
});