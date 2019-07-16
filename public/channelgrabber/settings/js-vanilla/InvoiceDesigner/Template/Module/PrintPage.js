define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PrintPage',
    'InvoiceDesigner/Template/PrintPage/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    PrintPageListener,
    printPageStorage,
    domManipulator
) {
    var PrintPage = function() {
        
        
        console.log('in printPage');
        
        
        ModuleAbstract.call(this);
        let storage = printPageStorage;


        this.setDomListener(new PrintPageListener());

        this.getStorage = function() {
            return storage;
        };

//        this.setAvailablePaperTypes = function(newAvailablePaperTypes) {
//            availablePaperTypes = newAvailablePaperTypes;
//        };

//        this.getAvailablePaperTypes = function() {
//            return availablePaperTypes;
//        };
    };

    PrintPage.MODULE_SELECTOR = '#printPageModule';

    PrintPage.prototype = Object.create(ModuleAbstract.prototype);

    PrintPage.prototype.init = function(template, templateService) {
        ModuleAbstract.prototype.init.call(this, template, templateService);

        let fetched = this.getStorage().fetchAll();
        console.log('fetched: ', fetched);
        
    };
    
    PrintPage.prototype.setPrintPageMargin = function(direction, value){
      console.log('in setPrintPageDimension', {direction,value});
      
       //todo - set these values to templates. Need to identify how to make something 'listen' to this change
    };

    return new PrintPage();
});