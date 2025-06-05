js = jQuery.noConflict();

var clearOptions = function(obj) {
    js(obj).closest('form').find("input[type=text], textarea").val("");
    js(obj).closest('form').find("select").each(function(){
          //your code here
        console.info(this.id);
        if (this.id.includes('filter_')) {
            js(this).find("option:selected").removeAttr('selected');
        }
    });
    js(obj).closest('form').submit();
}