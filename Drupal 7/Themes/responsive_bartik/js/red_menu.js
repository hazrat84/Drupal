var three_count = 0;
jQuery('nav#block-menu-menu-membership-menus ul.menu li a').each(function(){
    var site_lang = jQuery('html').attr('lang');
    var text = jQuery(this).text().split(' ');
    
    if(text.length < 2) // || site_lang == 'ar' || text.length ==3
        return;
    
    if(site_lang == 'ar' && text.length ==3 && three_count == 0){
        if(jQuery('nav#block-menu-menu-membership-menus ul.menu li a').attr('href') != '/ar/find-a-partner')
        text[2] = '<span style="color:red">'+text[2]+'</span>';
        three_count++;
    }
    
    if(text.length == 2)
    text[1] = '<span style="color:red">'+text[1]+'</span>';

    jQuery(this).html( text.join(' ') );

});