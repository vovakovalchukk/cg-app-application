function permissionsSelects() { 
    this.init = function(){
        handleClick();
    };
    
    var handleClick = function(){
        $('.permissions-select li').on('click', function(e){
           e.preventDefault();
           var li = $(this);
           if(li.hasClass('selected')){
               return false;
           }
           
           saveOption(li);
           
           if(li.closest('.permissions-select').hasClass('all')){
               var table = li.closest('table');
               var currentClass = li.html();
               console.log(table);
               console.log(currentClass);
               console.log('.permissions-select li.'+currentClass);
               console.log(table.find('.permissions-select li.'+currentClass).length);
               
               table.find('.permissions-select li.'+currentClass).each(function(){
                  saveOption($(this)); 
               });
           }
           return false; 
        });
    };
    
    var saveOption = function(li){
        li.siblings().removeClass('selected');
        showLoading(li);

        //DO AJAX HERE, on response do hideLoading($(this));
        setInterval(function(){
            hideLoading(li);
        },1200);

        li.addClass('selected');
    };

    var showLoading = function(li){
        li.closest('.permissions-select').addClass('loading');
    };

    var hideLoading = function(li){
        li.closest('.permissions-select').removeClass('loading');
    };
}

$(function(){
    var ps = new permissionsSelects();
    ps.init();
});
