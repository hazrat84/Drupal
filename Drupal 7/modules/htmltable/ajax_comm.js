// Change the page title.
$ajax_commands[] = ajax_command_invoke(NULL, 'changePageTitle', array(t('Users Listing')));

(function ($) {
  $.fn.changePageTitle = function(pageTitle) {
    document.title = pageTitle;
  };
})(jQuery);